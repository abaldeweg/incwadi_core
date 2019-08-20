<?php

namespace Incwadi\Core\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    /**
     * @Route("/v1/password", methods={"PUT"}, name="password")
     * @Security("is_granted('ROLE_USER')")
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $user = $this->getUser();
        $password = json_decode(
            $request->getContent(),
            true
        );
        $user->setPassword(
            $encoder->encodePassword(
                $user,
                $password['password']
            )
        );
        $this->getDoctrine()->getManager()->flush();

        return $this->json(
            [
                'msg' => 'Password changed successfully!'
                ]
            );
    }
}
