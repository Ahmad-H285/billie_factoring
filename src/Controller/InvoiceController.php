<?php

namespace App\Controller;

use App\Constant\HttpStatusCode;
use App\Entity\Invoice;
use App\Repository\CreditorRepository;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    /** @var CreditorRepository $creditorRepository */
    protected $creditorRepository;

    /** @var InvoiceRepository $invoiceRepository */
    protected $invoiceRepository;

    /**
     * CreditorController constructor.
     *
     * @param CreditorRepository $creditorRepository
     * @param InvoiceRepository $invoiceRepository
     */
    public function __construct(CreditorRepository $creditorRepository, InvoiceRepository $invoiceRepository)
    {
        $this->creditorRepository = $creditorRepository;
        $this->invoiceRepository  = $invoiceRepository;
    }

    /**
     * @Route("api/v1/invoice/{reference}", name="invoice_get", methods="GET")
     *
     * @param $reference
     *
     * @return Response
     */
    public function getInvoice($reference): Response
    {
        $invoice = $this->invoiceRepository->findOneBy(['reference' => $reference]);

        if(!$invoice) {
            return $this->json([
                'message' => 'Invoice not found',
            ], HttpStatusCode::NOT_FOUND);
        }

        return $this->json([
            'CreditorName' => $invoice->getCreditor()->getName(),
            'totalQuantoty' => $invoice->getTotalQuantity(),
            'totalCost' => $invoice->getCost(),
            'debtorPhone' => $invoice->getCustomerPhone(),
            'debtorEmail' =>  $invoice->getDebtorEmail() ? $invoice->getDebtorEmail() : '',
            'maxPaymentDays' => $invoice->getMaxPaymentDays(),
            'isPaid' => $invoice->isPaid(),
        ], HttpStatusCode::SUCCESS);
    }

    /**
     * @Route("api/v1/invoice/add", name="invoice_add", methods="POST")
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

        $invoiceExists = $this->invoiceExists($request->get('reference'));
        if($invoiceExists) {
            return $invoiceExists;
        }

        $this->hydrate($request, $em);

        return $this->json([
            'message' => 'Invoice added successfully',
        ], HttpStatusCode::SUCCESS);
    }


    /**
     * @Route("api/v1/invoice/pay", name="invoice_pay", methods="POST")
     *
     * @param Request                $request
     * @param EntityManagerInterface $em
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function pay(Request $request, EntityManagerInterface $em): Response
    {
        $invoiceReference = $request->get('referenceNo');

        if(!$invoiceReference) {
            return $this->json([
                'message' => 'Please provide invoice reference number'
            ], HttpStatusCode::BAD_REQUEST);
        }

        $payInvoice = $this->payInvoice($invoiceReference, $em);

        if(!$payInvoice) {
            return $this->json([
                'message' => 'No invoice found with this reference number',
            ], HttpStatusCode::NOT_FOUND);
        }

        return $this->json([
            'message' => 'Invoice paid successfully',
        ], HttpStatusCode::SUCCESS);
    }

    /**
     * @param Request $request
     *
     * @return bool|string
     */
    public function validate(Request $request)
    {
        $creditor = $this->creditorRepository->findOneBy(['code' => $request->get('creditorCode')]);

        if(!$creditor) {
            return 'Creditor code is missing';
        }

        if(!$request->get('reference') || $request->get('reference') == '') {
            return 'Invoice `reference` is missing';
        }

        if(!$request->get('totalCost') || $request->get('totalCost') == '') {
            return 'Invoice total cost is missing';
        }

        if(!$request->get('totalQuantity') || $request->get('totalQuantity') == '') {
            return 'Invoice total items quantity is missing';
        }

        if(!$request->get('maxPaymentDays') || $request->get('maxPaymentDays') == '') {
            return 'Invoice max payment days for payment is missing';
        }

        if(!$request->get('debtorPhone') || $request->get('debtorPhone') == '') {
            return 'Debtor phone is missing';
        }

        return false;
    }

    /**
     * @param string $reference
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|bool
     */
    private function invoiceExists(string $reference)
    {
        $invoice = $this->invoiceRepository->findOneBy(['reference' => $reference]);

        if($invoice) {
            return $this->json([
                'message' => 'Invoice `reference` already in use',
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
        $creditor = $this->creditorRepository->findOneBy(['code' => $request->get('creditorCode')]);

        $invoice = new Invoice();
        $invoice->setReference($request->get('reference'));
        $invoice->setCreditor($creditor);
        $invoice->setCost($request->get('totalCost'));
        $invoice->setTotalQuantity($request->get('totalQuantity'));
        $invoice->setMaxPaymentDays($request->get('maxPaymentDays'));
        $invoice->setDebtorEmail($request->get('debtorEmail'));
        $invoice->setCustomerPhone($request->get('debtorPhone'));
        $invoice->setCreatedAt(new \DateTimeImmutable(date('Y-m-d H:i:s')));
        $invoice->setPaid(0);

        $em->persist($invoice);
        $em->flush();
    }

    /**
     * @param string                 $invoiceNumber
     * @param EntityManagerInterface $em
     *
     * @return bool
     */
    private function payInvoice(string $invoiceNumber, EntityManagerInterface $em)
    {
        $invoice = $this->invoiceRepository->findOneBy(['reference' => $invoiceNumber]);
        
        if(!$invoice) {
            return false;
        }

        $invoice->setPaid(true);
        $em->persist($invoice);
        $em->flush();

        return true;
    }
}
