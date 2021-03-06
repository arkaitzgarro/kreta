<?php

/**
 * This file belongs to Kreta.
 * The source code of application includes a LICENSE file
 * with all information about license.
 *
 * @author benatespina <benatespina@gmail.com>
 * @author gorkalaucirica <gorka.lauzirika@gmail.com>
 */

namespace Kreta\Bundle\VCSBundle\Behat;

use Behat\Gherkin\Node\TableNode;
use Kreta\Bundle\CoreBundle\Behat\DefaultContext;

/**
 * Class CommitContext.
 *
 * @package Kreta\Bundle\VCSBundle\Behat
 */
class CommitContext extends DefaultContext
{
    /**
     * Populates the database with commits.
     *
     * @param \Behat\Gherkin\Node\TableNode $commits The commits
     *
     * @return void
     *
     * @Given /^the following commits exist:$/
     */
    public function theFollowingCommitsExist(TableNode $commits)
    {
        foreach ($commits as $commitData) {
            $branch = $this->getContainer()->get('kreta_vcs.repository.branch')
                ->findOneBy(['id' => $commitData['branch']]);
            $issuesRelated = [];
            if (isset($commitData['issuesRelated'])) {
                foreach (explode(',', $commitData['issuesRelated']) as $issueRelated) {
                    $issuesRelated[] = $this->getContainer()->get('kreta_issue.repository.issue')->find($issueRelated);
                }
            }

            $commit = $this->getContainer()->get('kreta_vcs.factory.commit')->create(
                $commitData['sha'], $commitData['message'], $branch, $commitData['author'], $commitData['url']
            );
            $this->setField($commit, 'issuesRelated', $issuesRelated);
            $this->setId($commit, $commitData['id']);

            $this->getContainer()->get('kreta_vcs.repository.commit')->persist($commit);
        }
    }
}
