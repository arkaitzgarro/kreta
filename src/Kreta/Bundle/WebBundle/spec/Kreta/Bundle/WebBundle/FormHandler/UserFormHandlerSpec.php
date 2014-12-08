<?php

/**
 * This file belongs to Kreta.
 * The source code of application includes a LICENSE file
 * with all information about license.
 *
 * @author benatespina <benatespina@gmail.com>
 * @author gorkalaucirica <gorka.lauzirika@gmail.com>
 */

namespace spec\Kreta\Bundle\WebBundle\FormHandler;

use Doctrine\Common\Persistence\ObjectManager;
use Kreta\Bundle\WebBundle\Event\FormHandlerEvent;
use Kreta\Component\Core\Factory\MediaFactory;
use Kreta\Component\Core\Model\Interfaces\MediaInterface;
use Kreta\Component\Core\Model\Interfaces\ProjectInterface;
use Kreta\Component\Core\Model\Interfaces\UserInterface;
use Kreta\Component\Core\Uploader\MediaUploader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserFormHandlerSpec.
 *
 * @package spec\Kreta\Bundle\WebBundle\FormHandler
 */
class UserFormHandlerSpec extends ObjectBehavior
{
    function let(FormFactory $formFactory, ObjectManager $manager,
                 EventDispatcherInterface $eventDispatcher, MediaFactory $mediaFactory,
                 MediaUploader $uploader)
    {
        $this->beConstructedWith($formFactory, $manager, $eventDispatcher, $mediaFactory, $uploader);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Kreta\Bundle\WebBundle\FormHandler\UserFormHandler');
    }

    function it_handles_form(Request $request, ProjectInterface $project, FormFactory $formFactory, FormInterface $form)
    {
        $formFactory->create(Argument::type('\Kreta\Bundle\CoreBundle\Form\Type\UserType'), $project)
            ->shouldBeCalled()->willReturn($form);
        $request->isMethod('POST')->shouldBeCalled()->willReturn(false);

        $this->handleForm($request, $project, null)->shouldReturn($form);
    }

    function it_handles_post_request(Request $request, UserInterface $user, FormFactory $formFactory,
                                     FormInterface $form, FileBag $fileBag,
                                     MediaFactory $mediaFactory, MediaInterface $media, ObjectManager $manager,
                                     MediaUploader $uploader, EventDispatcherInterface $eventDispatcher)
    {
        $image = new UploadedFile('', '', null, null, 99, true); //Avoids file not found exception
        $formFactory->create(Argument::type('\Kreta\Bundle\CoreBundle\Form\Type\UserType'), $user)
            ->shouldBeCalled()->willReturn($form);
        $request->isMethod('POST')->shouldBeCalled()->willReturn(true);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isSubmitted()->shouldBeCalled()->willReturn(true);
        $form->isValid()->shouldBeCalled()->willReturn(true);

        $fileBag->get('kreta_core_user_type')->shouldBeCalled()->willReturn(["photo" => $image]);
        $request->files = $fileBag;

        $mediaFactory->create($image)->shouldBeCalled()->willReturn($media);
        $uploader->upload($media)->shouldBeCalled();
        $user->setPhoto($media)->shouldBeCalled()->willReturn($user);

        $manager->persist($user)->shouldBeCalled();
        $manager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(
            FormHandlerEvent::NAME, Argument::type('\Kreta\Bundle\WebBundle\Event\FormHandlerEvent')

        );

        $this->handleForm($request, $user, null);
    }
}