<?php

namespace AppBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Place;
use AppBundle\Form\Type\PlaceFormType;

/**
 * Description of PlacesController
 *
 * @author Raibel Botta <raibelbotta@gmail.com>
 * @Route("/admin/lugares")
 */
class PlacesController extends Controller
{
    /**
     * @Route("/")
     * @Method({"GET"})
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('@App/Admin/Places/index.html.twig');
    }

    /**
     * @Route("/get-data")
     * @Method({"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getDataAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->getRepository('AppBundle:Place')
                ->createQueryBuilder('p')
                ->leftJoin('p.location', 'loc');

        $search = $request->get('search');
        $columns = $request->get('columns');
        $orders = $request->get('order', array());

        if ($search['value']) {
            $orX = $qb->expr()->orX();

            $orX->add($qb->expr()->like('p.name', ':q'));
            $orX->add($qb->expr()->like('p.postalAddress', ':q'));
            $orX->add($qb->expr()->andX($qb->expr()->isNotNull('p.location'),
                    $qb->expr()->like('loc.name', ':q')
                    ));

            $qb->setParameter('q', sprintf('%%%s%%', $search['value']));
            $qb->where($orX);
        }

        if ($orders) {
            $column = call_user_func(function($name) {
                if ($name == 'name') {
                    return 'p.name';
                } elseif ($name == 'postalAddress') {
                    return 'p.postalAddress';
                } elseif ('location' === $name) {
                    return 'loc.name';
                }

                return null;
            }, $columns[$orders[0]['column']]['name']);
            if (null !== $column) {
                $qb->orderBy($column, strtoupper($orders[0]['dir']));
            }
        }

        $paginator = $this->get('knp_paginator');
        $page = $request->get('start', 0) / $request->get('length') + 1;
        $pagination = $paginator->paginate($qb->getQuery(), $page, $request->get('length'));
        $total = $pagination->getTotalItemCount();

        $phoneService = $this->container->get('libphonenumber.phone_number_util');
        $template = $this->container->get('twig')->load('@App/Admin/Places/_cells.html.twig');
        $data = array_map(function(Place $place) use($phoneService, $template) {
            return array(
                $place->getName(),
                $place->getPostalAddress(),
                (string) $place->getLocation(),
                null !== $place->getMobilePhone() ? $phoneService->format($place->getMobilePhone(), \libphonenumber\PhoneNumberFormat::NATIONAL) : '',
                null !== $place->getFixedPhone() ? $phoneService->format($place->getFixedPhone(), \libphonenumber\PhoneNumberFormat::NATIONAL) : '',
                $template->renderBlock('actions', array('record' => $place))
            );
        }, $pagination->getItems());

        return new JsonResponse(array(
            'data' => $data,
            'draw' => $request->get('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $total
        ));
    }

    /**
     * @Route("/{id}/ver", requirements={"id": "\d+"})
     * @Method({"GET"})
     * @param Place $record
     * @return Response
     */
    public function viewAction(Place $record)
    {
        return $this->render('@App/Admin/Places/view.html.twig', array(
            'record' => $record
        ));
    }

    /**
     * @Route("/nuevo")
     * @Method({"get", "post"})
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();

        $place = new Place();
        $place->setEnterprise($manager->getRepository('AppBundle:Enterprise')->findOneBy(array()));
        $form = $this->createForm(PlaceFormType::class, $place);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($form->getData());
            $manager->flush();

            $this->addFlash('notice', 'Registro creado');

            return $this->redirect($this->generateUrl('app_admin_places_index'));
        }

        return $this->render('@App/Admin/Places/new.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/{id}/editar", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @param Place $record
     * @param Request $request
     * @return Response
     */
    public function editAction(Place $record, Request $request)
    {
        $form = $this->createForm(PlaceFormType::class, $record);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            return $this->redirect($this->generateUrl('app_admin_places_index'));
        }

        return $this->render('@App/Admin/Places/edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/{id}/eliminar", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @return JsonResponse
     */
    public function deleteAction(Place $record)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($record);
        $em->flush();

        return new JsonResponse(array(
            'result' => 'success'
        ));
    }
}
