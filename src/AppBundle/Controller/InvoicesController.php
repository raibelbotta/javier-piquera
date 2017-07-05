<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Form\Type\InvoiceType;
use AppBundle\Entity\Reserva;

/**
 * Description of  InvoicesController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/facturas")
 */
class InvoicesController extends Controller
{
    /**
     * @Route("/")
     * @Method({"get"})
     * @return array
     */
    public function indexAction()
    {
        return $this->render('App/Invoices/index.html.twig');
    }

    /**
     * @Route("/obtener-datos")
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getDataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AppBundle:Reserva')
                ->createQueryBuilder('r')
                ->join('r.serviceType', 'st')
                ->join('r.provider', 'p');

        $andX = $qb->expr()->andX(
            $qb->expr()->isNotNull('r.invoicedAt')
        );

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());

        if ($search['value']) {
            $orX = $qb->expr()->orX(
                $qb->expr()->like('r.invoiceNumber', $qb->expr()->literal("%{$search['value']}%")),
                $qb->expr()->like('st.name', $qb->expr()->literal("%{$search['value']}%")),
                $qb->expr()->like('p.name', $qb->expr()->literal("%{$search['value']}%")),
                $qb->expr()->like('r.providerReference', $qb->expr()->literal("%{$search['value']}%"))
            );

            $andX->add($orX);
        }

        $qb->where($andX);

        if ($orders) {
            $column = call_user_func(function($name) use ($qb) {
                if ($name == 'invoicedAt') {
                    return 'r.invoicedAt';
                } elseif ($name == 'invoiceNumber') {
                    return 'r.invoiceNumber';
                } elseif ($name == 'provider') {
                    return 'p.name';
                } elseif ($name == 'serviceType') {
                    return 'st.name';
                } else {
                    return null;
                }
            }, $columns[$orders[0]['column']]['name']);
            if (null !== $column) {
                $qb->orderBy($column, strtoupper($orders[0]['dir']));
            }
        }

        $paginator = $this->get('knp_paginator');
        $page = $request->get('start', 0) / $request->get('length') + 1;
        $pagination = $paginator->paginate($qb->getQuery(), $page, $request->get('length'));

        $total = $pagination->getTotalItemCount();

        $data = array();

        foreach ($pagination->getItems() as $record) {
            $row = array(
                $record->getInvoicedAt()->format('d/m/Y H:i'),
                $record->getInvoiceNumber(),
                $record->getProvider()->getName(),
                $record->getProviderReference(),
                sprintf('<div title="%s">%s</div>', $record->getServiceDescription(), $record->getServiceType()->getName()),
                sprintf('<div class="text-right">%0.2f</div>', $record->getInvoicedTotalPrice()),
                $this->renderView('App/Invoices/_index_actions.html.twig', array(
                    'record' => $record
                ))
            );

            $data[] = $row;
        }

        return new JsonResponse(array(
            'data' => $data,
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total
        ));
    }

    /**
     * @Route("/preparar")
     * @Method({"get"})
     * @return Response
     */
    public function prepareAction()
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AppBundle:Reserva')
                ->createQueryBuilder('r')
                ->select('p.id, p.name')
                ->join('r.provider', 'p')
                ->groupBy('p.id', 'p.name')
                ->orderBy('p.name');
        $qb->where($qb->expr()->eq('p.receiveInvoice', $qb->expr()->literal(true)));

        return $this->render('App/Invoices/prepare.html.twig', array(
            'providers' => $qb->getQuery()->getResult()
        ));
    }

    /**
     * @Route("/obtener-servicios-por-agencia")
     * @Method({"post"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getServicesByProviderAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = array();
        $qb = $em->getRepository('AppBundle:Reserva')
                ->createQueryBuilder('r')
                ->select('r, st')
                ->join('r.serviceType', 'st')
                ->orderBy('r.startAt', 'DESC');
        $qb->where($qb->expr()->andX(
            $qb->expr()->eq('r.provider', $qb->expr()->literal($request->get('provider'))),
            $qb->expr()->isNull('r.invoicedAt')
        ));
        $query = $qb->getQuery();

        foreach ($query->getResult() as $record) {
            $data[] = array(
                'value' => $record->getId(),
                'text' => sprintf('%s | %s | %s', $record->getSerialNumber(), $record->getStartAt()->format('d/m/Y H:i'), $record->getServiceType()->getName())
            );
        }

        return new JsonResponse(array(
            'data' => $data
        ));
    }

    /**
     * @Route("/obtener-formulario-factura")
     * @Method({"post"})
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function getInvoiceFormAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $record = $em->find('AppBundle:Reserva', $request->get('id'));

        if ($record->getInvoicedAt()) {
            return new RedirectResponse($this->generateUrl('app_invoices_getfinishedinvoice', array('id' => $record->getId())));
        }

        $record
                ->setInvoicedKilometers(0)
                ->setInvoicedHours(0)
                ->setInvoicedKilometersPrice(0)
                ->setInvoicedKilometerPrice(0)
                ->setInvoicedHourPrice(0)
                ->setInvoicedHoursPrice(0)
                ->setInvoicedTotalPrice(0)
                ->setInvoiceDriver($record->getDriver());

        $form = $this->createForm(new InvoiceType($em), $record);

        return $this->render('App/Invoices/invoice_form.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/crear-factura/{id}", requirements={"id": "\d+"})
     * @ParamConverter("record", class="AppBundle\Entity\Reserva")
     * @param Reserva $reserva
     * @param Request $request
     * @return RedirectResponse
     */
    public function createAction(Reserva $record, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $enterprise = $record->getEnterprise();
        $form = $this->createForm(new InvoiceType($em), $record);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $invoiceNumber = $enterprise->getLastInvoiceNumber() + 1;
            $record
                ->setInvoicedAt(new \DateTime('now'))
                ->setInvoiceNumber(sprintf('%04d/%s', $invoiceNumber, date('Y')));
            $enterprise->setLastInvoiceNumber($invoiceNumber);

            $em->flush();

            return $this->redirectToRoute('app_invoices_getfinishedinvoice', array(
                'id' => $record->getId()
            ));
        }

        return $this->render('App/Invoices/invoice_form.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/ver-factura-terminada", requirements={"id": "\d+"})
     * @ParamConverter("record", class="AppBundle\Entity\Reserva")
     * @param Reserva $record
     * @return array
     */
    public function getFinishedInvoiceAction(Reserva $record)
    {
        return $this->render('App/Invoices/invoice_finished.html.twig', array(
            'record' => $record
        ));
    }
}
