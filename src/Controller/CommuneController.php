<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Commune;
use OpenApi\Annotations as OA;
use App\Repository\MediaRepository;
use App\Repository\CommuneRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommuneController extends AbstractController
{
  
    protected function serializeJson($objet)
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
     * @Route("/commune", name="commune_json", methods={"GET"})
     * @param CommuneRepository $CommuneRepository
     * @param Request $request
     * @return Response
     */
    public function communesJson(CommuneRepository $communeRepository, Request $request)
    {
        $filter = [];
        $em = $this->getDoctrine()->getManager();
        $metadata = $em->getClassMetadata(Commune::class)->getFieldNames();
        foreach ($metadata as $value) {
            if ($request->query->get($value)) {
                $filter[$value] = $request->query->get($value);
            }
        }
        return JsonResponse::fromJsonString($this->serializeJson($communeRepository->findBy($filter)));
    }
    /**
     * @Route("/api/admin/commune/create", name="commune_Create", methods={"PUT"})
     * @param Request $request
     * @return Response
     */
    public function communeCreate(Request $request)
    {


        $entityManager = $this->getDoctrine()->getManager();

        $newCommune = new Commune();
        $data = json_decode(
            $request->getContent(),
            true
        );
        $newCommune->setNom($data["nom"])
            ->setCode($data["code"])
            ->setCodeDepartement($data["codeDepartement"])
            ->setcodeRegion($data["codeRegion"])
            ->setCodesPostaux($data["codesPostaux"])
            ->setPopulation($data["population"]);
            if ($data['media']) {
                $arrayMedia = $data['media'];
                for ($i = 0;$i < count($arrayMedia);$i++){
                    $dataMedia = $arrayMedia[$i];
                    $media = new Media();
                    $media->setCommune($newCommune)
                        ->setImage($dataMedia['image']);
                    $entityManager->persist($media);
                }
            }

        $entityManager->persist($newCommune);
        $entityManager->flush();

        $response = new Response();
        $response->setContent('Saved new commune with id ' . $newCommune->getId());
        return $response;
    }

    /**
     * @Route("/api/admin/commune/update", name="commune_put", methods={"PATCH"})
     * @param Request $request
     * @param CommuneRepository $communeRepository
     * @return JsonResponse
     */
    public function communeUpdate(Request $request, CommuneRepository $communeRepository)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(),true);
        $commune = $communeRepository->findOneBy(['id' => $data['commune_id']]);
        isset($data["nom"]) && $commune->setNom($data['nom']);
        isset($data["code"]) && $commune->setCode($data['code']);
        isset($data["codesPostaux"]) && $commune->setCodesPostaux($data['codesPostaux']);
        isset($data["population"]) && $commune->setPopulation($data['population']);
        isset($data["codeDepartement"]) && $commune->setCodeDepartement($data['codeDepartement']);
        isset($data["codeRegion"]) && $commune->setcodeRegion($data['codeRegion']);
    
  
        $entityManager->persist($commune);
        $entityManager->flush();
        return JsonResponse::fromJsonString($this->serializeJson($commune));
       
    }

    /**
     * @Route("/api/admin/commune/delete", name="commune_delete", methods={"DELETE"})
     * @param Request $request
     * @param CommuneRepository $communeRepository
     * @return Response
     */
    public function departementDelete(Request $request, CommuneRepository $communeRepository)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $response = new Response();
        $data = json_decode(
            $request->getContent(),
            true
        );
        if (isset($data["id"])) {
            $commune = $communeRepository->find($data["id"]);
            if ($commune === null) {
                $response->setContent("Cet commune n'existe pas");
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            } else {
                $entityManager->remove($commune);
                $entityManager->flush();
                $response->setContent("Cet commune à était supprimé");
                $response->setStatusCode(Response::HTTP_OK);
            }
        } else {
            $response->setContent("L'id n'est pas renseigné");
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }
}
