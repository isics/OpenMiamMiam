<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Super;

use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\CategoryType;
use Isics\Bundle\OpenMiamMiamBundle\Model\Category\CategoryNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends Controller
{
    /**
     * List categories
     *
     * @return Response
     */
    public function listAction()
    {
        $categories = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Category')->getNodesHierarchy();

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Category:list.html.twig', array(
            'categories' => $categories,
        ));
    }

    /**
     * Create Category
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $categoryManager = $this->get('open_miam_miam.category_manager');
        $categoryNode = $categoryManager->createNode();

        $form = $this->getForm($categoryNode);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $categoryManager->saveNode($categoryNode, $this->get('security.token_storage')->getToken()->getUser());
                $this->get('session')->getFlashBag()->add('notice', 'admin.super.category.message.created');

                return $this->redirect($this->generateUrl('open_miam_miam.admin.super.category.list'));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Category:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Edit Category
     *
     * @ParamConverter("category", class="IsicsOpenMiamMiamBundle:Category", options={"mapping": {"categoryId": "id"}})
     *
     * @param Request  $request
     * @param Category $category
     *
     * @return Response
     */
    public function editAction(Request $request, Category $category)
    {
        $categoryManager = $this->get('open_miam_miam.category_manager');

        $categoryNode = $categoryManager->createNode($category);

        $form = $this->getForm($categoryNode);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $categoryManager->saveNode($categoryNode, $this->get('security.token_storage')->getToken()->getUser());
                $this->get('session')->getFlashBag()->add('notice', 'admin.super.category.message.updated');

                return $this->redirect($this->generateUrl('open_miam_miam.admin.super.category.list'));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Category:edit.html.twig', array(
            'form'       => $form->createView(),
            'activities' => $categoryManager->getActivities($category),
        ));
    }

    /**
     * Return category form
     *
     * @param CategoryNode $categoryNode
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function getForm(CategoryNode $categoryNode)
    {
        if (null === $categoryNode->getCategory()->getId()) {
            $action = $this->generateUrl(
                'open_miam_miam.admin.super.category.create'
            );
        } else {
            $action = $this->generateUrl(
                'open_miam_miam.admin.super.category.edit',
                array('categoryId' => $categoryNode->getCategory()->getId())
            );
        }

        return $this->container->get('form.factory')
            ->createNamedBuilder(
                'open_miam_miam_category',
                CategoryType::class,
                $categoryNode,
                array('action' => $action, 'method' => 'POST')
            )
            ->getForm();
    }

    /**
     * Delete Category
     *
     * @ParamConverter("category", class="IsicsOpenMiamMiamBundle:Category", options={"mapping": {"categoryId": "id"}})
     *
     * @param Category $category
     *
     * @return Response
     */
    public function deleteAction(Category $category)
    {
        $categoryManager = $this->get('open_miam_miam.category_manager');

        try {
            $categoryManager->delete($category);
            $this->get('session')->getFlashBag()->add('notice', 'admin.super.category.message.deleted');
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', 'admin.super.category.message.unable_to_delete');
        }

        return $this->redirect($this->generateUrl('open_miam_miam.admin.super.category.list'));
    }
}
