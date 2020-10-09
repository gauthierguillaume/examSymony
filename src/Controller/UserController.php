<?php

namespace App\Controller;


use App\Repository\UserRepository;
use App\Repository\CommuneRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use App\Entity\User;
use App\Entity\Media;
use App\Entity\Commune;
use OpenApi\Annotations as OA;


class UserController extends AbstractController
{
    private function serializeJson($objet)
    {
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getNom();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        return $serializer->serialize($objet, 'json');
    }
    /**
     * @Route("user/admin/create", name="userCreate", methods={"PUT"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return JsonResponse
     */
    public function userCreate(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = new User();
        $data = json_decode($request->getContent(), true);
        $user
            ->setEmail($data["email"])
            ->setRoles(['ROLE_USER'])
            ->setPassword($passwordEncoder->encodePassword($user, $data["password"]));
        $entityManager->persist($user);
        $entityManager->flush();
        return JsonResponse::fromJsonString($this->serializeJson($user));
    }
    /**
     * @Route("/api/admin/user/update", name="userUpdate", methods={"PATCH"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return JsonResponse
     */
    public function userUpdate(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $item = json_decode($request->getContent(), true);
        $user = $userRepository->findOneBy(['id' => $item['id']]);

        isset($item["email"]) && $user->setEmail($item['email']);
        isset($item["password"]) && $user->setPassword($passwordEncoder->encodePassword($user, $item["password"]));

        $entityManager->persist($user);
        $entityManager->flush();

        return JsonResponse::fromJsonString($this->serializeJson($user));
    }
    /**
     * @Route("/api/admin/user/delete", name="userDelete", methods={"DELETE"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function userDelete(Request $request, UserRepository $userRepository)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $item = json_decode($request->getContent(), true);
        $user = $userRepository->find($item['id']);
        $response = new Response();
        if ($user) {
            $entityManager->remove($user);
            $entityManager->flush();
            $response
                ->setContent("l'utilisateur avec l'id " . $item['id'] ." est supprimé")
                ->setStatusCode(Response::HTTP_OK);
        } else {
            $response
                ->setContent('bad request')
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }
}
