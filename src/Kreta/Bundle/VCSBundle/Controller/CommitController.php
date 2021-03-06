<?php

/**
 * This file belongs to Kreta.
 * The source code of application includes a LICENSE file
 * with all information about license.
 *
 * @author benatespina <benatespina@gmail.com>
 * @author gorkalaucirica <gorka.lauzirika@gmail.com>
 */

namespace Kreta\Bundle\VCSBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\View;
use Kreta\Bundle\CoreBundle\Controller\RestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class CommitController.
 *
 * @package Kreta\Bundle\VCSBundle\Controller
 */
class CommitController extends RestController
{
    /**
     * Returns all commits of project id and issue id given.
     *
     * @param string $projectId The project id
     * @param string $issueId   The issue id
     *
     * @ApiDoc(
     *  description = "Returns all commits of issue id given",
     *  requirements = {
     *    {
     *      "name"="_format",
     *      "requirement"="json|jsonp",
     *      "description"="Supported formats, by default json"
     *    }
     *  },
     *  resource = true,
     *  statusCodes = {
     *    200 = "<data>",
     *    403 = "Not allowed to access this resource",
     *    404 = "Does not exist any object with id passed"
     *  }
     * )
     *
     * @Get("/projects/{projectId}/issues/{issueId}/vcs-commits")
     *
     * @View(
     *  statusCode=200,
     *  serializerGroups={"commitList"}
     * )
     *
     * @return \Kreta\Component\VCS\Model\Interfaces\CommitInterface[]
     */
    public function getCommitsAction($projectId, $issueId)
    {
        $issue = $this->getIssueIfAllowed($projectId, $issueId);

        return $this->get('kreta_vcs.repository.commit')->findByIssue($issue);
    }
}
