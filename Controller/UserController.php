<?php

namespace FrontBundle\Controller;

use AdminBundle\Entity\User;
use AdminBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FrontBundle\Service\FrontService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
/**
 * Class UserController
 * @Route("/user")
 * @package FrontBundle\Controller")
 */
class UserController extends Controller
{
    /**
     * @Route("/index", name="front_user_index")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {

        $lp = $this->get('front.service')->getListPageFactory();
        $lp->setParameter('sort', 'created_at');
        $lp->setParameter('dir', 'DESC');
        $parametersRecent = $lp->getParameters(true);

        $lp2 = $this->get('front.service')->getListPageFactory();
        $lp2->setParameter('sort', 'visits');
        $lp2->setParameter('dir', 'DESC');
        $parametersVisits = $lp2->getParameters(true);

        return $this->render('FrontBundle:Main:index.html.twig', [
            'parametersRecent' => $parametersRecent,
            'parametersVisits' => $parametersVisits
        ]);
    }

    /**
     * Register user
     *
     * @param Request $request
     * @Route("/register", name="front_user_register")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        $entity = new User();
        $entity->setEnabled(true);
        $entity->setDateOfBirth(new \DateTime());
        $countries = $this->get('locale.service')->getCountries($request->getLocale());
        $form = $this->createForm(UserType::class, $entity, ['countries' => $countries]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');
            $userManager->updateUser($entity);

            return $this->redirectToRoute('front_main_index', ['id' => $entity->getId()]);
        }

        return $this->render('FrontBundle:Main:register.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @Route("/login", name="front_user_login")
     */
    public function loginAction(Request $request)
    {

        //return $this->render('FrontBundle:User:user_details.html.twig');

    }

    /**
     * Get user details
     *
     * @param Request $request
     * @param $id
     * @Route("/user-details/{id}", name="front_user_details")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userDetailsAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $userDetails = $em->getRepository('AdminBundle:User')->find($id);
        $booksReading = $em->getRepository('SmartlibBundle:Book')->getFront()->getReadingProgress($userDetails);
        $downloadedBooks = $em->getRepository('SmartlibBundle:Book')->getFront()->getDownloadedBooks($userDetails);
        $favoriteBooks = $em->getRepository('SmartlibBundle:Book')->getFront()->getFavoriteBooks($id);
        $followingAuthors = $em->getRepository('SmartlibBundle:Person')->getFront()->getFollowAuthors($id);
        $followingTranslators = $em->getRepository('SmartlibBundle:Person')->getFront()->getFollowTranslators($id);
        $userComments = $em->getRepository('CommentBundle:Comment')->getFront()->getUserComments($id);

        return $this->render('FrontBundle:User:user_details.html.twig', [
            'userDetails'           => $userDetails,
            'booksReading'          => $booksReading,
            'downloadedBooks'       => $downloadedBooks,
            'favoriteBooks'         => $favoriteBooks,
            'followingAuthors'      => $followingAuthors,
            'followingTranslators'  => $followingTranslators,
            'userComments'          => $userComments
        ]);
    }

    /**
     * Follow author
     *
     * @param Request $request
     * @param $id
     * @Route("/follow-author/{id}", name="front_user_follow_author")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function followAuthorAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AdminBundle:User')->find($this->getUser()->getId());

        $author = $em->getRepository('SmartlibBundle:Person')->find($id);
        $author->addFollower($user);
        $em->persist($author);
        $em->flush();

        return $this->redirectToRoute('front_main_index');

    }

    /**
     * Unfollow author
     *
     * @param Request $request
     * @param $id
     * @Route("/unfollow-author/{id}", name="front_user_unfollow_author")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unFollowAuthorAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AdminBundle:User')->find($this->getUser()->getId());
        $author = $em->getRepository('SmartlibBundle:Person')->find($id);
        $author->removeFollower($user);
        $em->persist($author);
        $em->flush();

        return $this->redirectToRoute('front_main_index');

    }

    /**
     * @param Request $request
     * @Route("/follow-translator", name="front_user_follow_translator")
     */
    public function followTranslatorAction(Request $request)
    {

    }


    /**
     * Reset password with sending email
     *
     * @param Request $request
     * @Route("/reset-password-send-email", name="user_reset_password_send_email")
     * @return JsonResponse
     */
    public function resetPasswordSendEmailAction(Request $request)
    {
        $username = $request->request->get('username');

        /** @var $user UserInterface */
        $user = $this->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        if(!$user){
            return new JsonResponse([
                'success'=>false
            ]);
        }
        /* Dispatch init event */
        $event = new GetResponseNullableUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            //return $event->getResponse();
        }

        $ttl = $this->container->getParameter('fos_user.resetting.retry_ttl');

        if (null !== $user && !$user->isPasswordRequestNonExpired($ttl)) {
            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_REQUEST, $event);

            if (null !== $event->getResponse()) {
                //return $event->getResponse();
            }

            if (null === $user->getConfirmationToken()) {
                /** @var $tokenGenerator TokenGeneratorInterface */
                $tokenGenerator = $this->get('fos_user.util.token_generator');
                $user->setConfirmationToken($tokenGenerator->generateToken());
            }

            /* Dispatch confirm event */
            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_CONFIRM, $event);

            if (null !== $event->getResponse()) {
                //return $event->getResponse();
            }

            $this->get('fos_user.mailer')->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new \DateTime());
            $this->get('fos_user.user_manager')->updateUser($user);

            /* Dispatch completed event */
            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_COMPLETED, $event);

            if (null !== $event->getResponse()) {
                //return $event->getResponse();
            }
        }

        return new JsonResponse([
            'success'=>true
        ]);
    }

}