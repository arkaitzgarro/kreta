<?php

/**
 * This file belongs to Kreta.
 * The source code of application includes a LICENSE file
 * with all information about license.
 *
 * @author benatespina <benatespina@gmail.com>
 * @author gorkalaucirica <gorka.lauzirika@gmail.com>
 */

namespace spec\Kreta\Component\Core\Exception;

use PhpSpec\ObjectBehavior;

/**
 * Class CollectionMinLengthExceptionSpec.
 *
 * @package spec\Kreta\Component\Core\Exception
 */
class CollectionMinLengthExceptionSpec extends ObjectBehavior 
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Kreta\Component\Core\Exception\CollectionMinLengthException');
    }

    function it_extends_exception()
    {
        $this->shouldHaveType('Exception');
    }
}
