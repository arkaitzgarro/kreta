<?php

/**
 * This file belongs to Kreta.
 * The source code of application includes a LICENSE file
 * with all information about license.
 *
 * @author benatespina <benatespina@gmail.com>
 * @author gorkalaucirica <gorka.lauzirika@gmail.com>
 */

namespace Kreta\Bundle\WebBundle\Controller;

use Kreta\Component\Issue\Model\Interfaces\IssueInterface;
use Kreta\Component\Workflow\Model\Interfaces\StatusTransitionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class IssueController.
 *
 * @package Kreta\Bundle\WebBundle\Controller
 */
class IssueController extends Controller
{
    /**
     * View action.
     *
     * @param string $projectShortName Project short name
     * @param string $issueNumber      The issue number
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Template
     */
    public function viewAction($projectShortName, $issueNumber)
    {
        $issue = $this->get('kreta_issue.repository.issue')->findOneByShortCode($projectShortName, $issueNumber);

        if (!$issue instanceof IssueInterface) {
            throw $this->createNotFoundException();
        }

        if (!$this->get('security.context')->isGranted('view', $issue)) {
            throw new AccessDeniedException();
        };

        return ['issue' => $issue];
    }

    /**
     * New action.
     *
     * @param string                                    $projectShortName Project shortname
     * @param \Symfony\Component\HttpFoundation\Request $request          The request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Template
     */
    public function newAction($projectShortName, Request $request)
    {
        /** @var \Kreta\Component\Project\Model\Interfaces\ProjectInterface $project */
        $project = $this->get('kreta_project.repository.project')->findOneBy([
            'shortName' => $projectShortName
        ]);

        if (!$project || !$this->get('security.context')->isGranted('create_issue', $project)) {
            throw new AccessDeniedException();
        }

        $issue = $this->get('kreta_issue.factory.issue')->create($project, $this->getUser());

        $form = $this->get('kreta_issue.form_handler.issue')->handleForm(
            $request, $issue, ['participants' => $project->getParticipants()]
        );

        if ($form->isValid()) {
            return $this->redirect($this->generateUrl(
                'kreta_web_issue_view', [
                'projectShortName' => $issue->getProject()->getShortName(),
                'issueNumber'      => $issue->getNumericId()
            ]));
        }

        return ['form' => $form->createView(), 'project' => $project];
    }

    /**
     * Edit action.
     *
     * @param string                                    $projectShortName Project shortname
     * @param string                                    $issueNumber      The issue number
     * @param \Symfony\Component\HttpFoundation\Request $request          The request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Template
     */
    public function editAction($projectShortName, $issueNumber, Request $request)
    {
        $issue = $this->get('kreta_issue.repository.issue')->findOneByShortCode($projectShortName, $issueNumber);

        if (!$issue instanceof IssueInterface) {
            throw $this->createNotFoundException();
        }

        if (!$this->get('security.context')->isGranted('edit', $issue)) {
            throw new AccessDeniedException();
        };

        $form = $this->get('kreta_issue.form_handler.issue')->handleForm(
            $request, $issue, ['participants' => $issue->getProject()->getParticipants()]
        );

        if ($form->isValid()) {
            return $this->redirect($this->generateUrl(
                'kreta_web_issue_view', [
                    'projectShortName' => $issue->getProject()->getShortName(),
                    'issueNumber'      => $issue->getNumericId()
                ]
            ));
        }

        return ['form' => $form->createView(), 'issue' => $issue];
    }

    /**
     * Edit status action.
     *
     * @param string $projectShortName Project short name
     * @param string $issueNumber      The issue number
     * @param string $transitionId     The transition id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editStatusAction($projectShortName, $issueNumber, $transitionId)
    {
        $issue = $this->get('kreta_issue.repository.issue')->findOneByShortCode($projectShortName, $issueNumber);

        if (!$issue instanceof IssueInterface) {
            $this->createNotFoundException();
        }

        if (!$this->get('security.context')->isGranted('edit', $issue)) {
            throw new AccessDeniedException();
        };

        $transition = $this->get('kreta_workflow.repository.status_transition')->find($transitionId);

        if (!$transition instanceof StatusTransitionInterface ||
            $issue->getProject()->getWorkflow()->getId() !== $transition->getWorkflow()->getId()
        ) {
            $this->createNotFoundException();
        }

        $statuses = $this->get('kreta_workflow.repository.status')
            ->findBy(['workflow' => $issue->getProject()->getWorkflow()]);
        $transitions = $this->get('kreta_workflow.repository.status_transition')
            ->findBy(['workflow' => $issue->getProject()->getWorkflow()]);

        $stateMachine = $this->get('kreta_issue.state_machine.issue')->load($issue, $statuses, $transitions);

        if ($stateMachine->can($transition->getName())) {
            $stateMachine->apply($transition->getName());
            $this->getDoctrine()->getManager()->flush();
            $this->get('session')->getFlashBag()->add('success', 'Status changed successfully');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Transition not allowed');
        }

        return $this->redirect($this->generateUrl(
            'kreta_web_issue_view', [
                'projectShortName' => $issue->getProject()->getShortName(),
                'issueNumber'      => $issue->getNumericId()
            ]
        ));
    }
}
