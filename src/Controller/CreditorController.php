<?php

namespace App\Controller;

use App\Constant\HttpStatusCode;
use App\Entity\Creditor;
use App\Repository\CreditorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreditorController extends AbstractController
{
    /** @var CreditorRepository $creditorRepository */
    protected $creditorRepository;

    /**
     * CreditorController constructor.
     *
     * @param CreditorRepository $creditorRepository
     */
    public function __construct(CreditorRepository $creditorRepository)
    {
        $this->creditorRepository = $creditorRepository;
    }

    /**
     * @Route("api/v1/creditor/{code}", name="creditor_get", methods="GET")
     *
     * @param $code
     *
     * @return Response
     */
    public function getCreditor($code): Response
    {
        $creditor = $this->creditorRepository->findOneBy(['code' => $code]);

        if(!$creditor) {
            return $this->json([
                'message' => 'Creditor not found',
            ], HttpStatusCode::NOT_FOUND);
        }

        return $this->json([
            'code' => $creditor->getCode(),
            'name' => $creditor->getName(),
        ], HttpStatusCode::SUCCESS);
    }

    /**
     * @Route("api/v1/creditor/add", name="creditor_add", methods="POST")
     *
     * @param Request                $request
     * @param EntityManagerInterface $em
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $validateError = $this->validate($request);
        if($validateError) {
            return $this->json([
                'message' => $validateError
            ], HttpStatusCode::BAD_REQUEST);
        }

        $creditorExits = $this->creditorExists($request->get('code'));
        if($creditorExits) {
            return $creditorExits;
        }

        $this->hydrate($request, $em);

        return $this->json([
            'message' => 'Creditor added successfully',
        ], HttpStatusCode::SUCCESS);
    }

    /**
     * @param Request $request
     *
     * @return bool|string
     */
    public function validate(Request $request)
    {
        if(!$request->get('code') || $request->get('code') == '') {
            return 'Creditor `code` is missing';
        }

        if(!$request->get('name') || $request->get('name') == '') {
            return 'Creditor `name` is missing';
        }

        return false;
    }

    /**
     * @param string $code
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|bool
     */
    private function creditorExists(string $code)
    {
        $creditor = $this->creditorRepository->findOneBy(['code' => $code]);

        if($creditor) {
            return $this->json([
                'message' => 'Creditor `code` already in use',
            ], HttpStatusCode::CONFLICT);
        }

        return false;
    }

    /**
     * @param Request                $request
     * @param EntityManagerInterface $em
     */
    private function hydrate(Request $request, EntityManagerInterface $em)
    {
        $creditor = new Creditor();
        $creditor->setCode($request->get('code'));
        $creditor->setName($request->get('name'));
        $creditor->setDebtorLimit($request->get('debtorLimit'));

        $em->persist($creditor);
        $em->flush();
    }
}
