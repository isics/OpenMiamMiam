<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Association;

use Isics\Bundle\OpenMiamMiamBundle\Controller\Admin\Association\BaseController;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class BranchController extends BaseController
{
    /**
     * Secures branch for association
     *
     * @param Association $association
     * @param Branch      $branch
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function secureBranch(Association $association, Branch $branch)
    {
        if ($association->getId() !== $branch->getAssociation()->getId()) {
            throw $this->createNotFoundException('Invalid branch for association');
        }
    }

    /**
     * List branches
     *
     * @param Association $association
     *
     * @return Response
     */
    public function listAction(Association $association)
    {
        $this->secure($association);

        $branches = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Branch')->findForAssociation($association);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Branch:list.html.twig', array(
            'association' => $association,
            'branches'    => $branches
        ));
    }

    // /**
    //  * Create article
    //  *
    //  * @param Request     $request
    //  * @param Association $association
    //  *
    //  * @return Response
    //  */
    // public function createAction(Request $request, Association $association)
    // {
    //     $this->secure($association);

    //     $articleManager = $this->get('open_miam_miam.article_manager');
    //     $article = $articleManager->createForAssociation($association);

    //     $form = $this->getForm($article);
    //     if ($request->isMethod('POST')) {
    //         $form->handleRequest($request);
    //         if ($form->isValid()) {
    //             $articleManager->save($article, $this->get('security.context')->getToken()->getUser());

    //             $this->get('session')->getFlashBag()->add('notice', 'admin.association.articles.message.created');

    //             return $this->redirect($this->generateUrl(
    //                 'open_miam_miam.admin.association.article.edit',
    //                 array('id' => $association->getId(), 'articleId' => $article->getId())
    //             ));
    //         }
    //     }

    //     return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Article:create.html.twig', array(
    //         'association' => $association,
    //         'form'        => $form->createView(),
    //     ));
    // }

    // /**
    //  * Edit article
    //  *
    //  * @ParamConverter("article", class="IsicsOpenMiamMiamBundle:Article", options={"mapping": {"articleId": "id"}})
    //  *
    //  * @param Request     $request
    //  * @param Association $association
    //  * @param Article     $article
    //  *
    //  * @return Response
    //  */
    // public function editAction(Request $request, Association $association, Article $article)
    // {
    //     $this->secure($association);
    //     $this->secureArticle($association, $article);

    //     $articleManager = $this->get('open_miam_miam.article_manager');

    //     $form = $this->getForm($article);
    //     if ($request->isMethod('POST')) {
    //         $form->handleRequest($request);
    //         if ($form->isValid()) {
    //             $articleManager->save($article, $this->get('security.context')->getToken()->getUser());

    //             $this->get('session')->getFlashBag()->add('notice', 'admin.association.articles.message.updated');

    //             return $this->redirect($this->generateUrl(
    //                 'open_miam_miam.admin.association.article.edit',
    //                 array('id' => $association->getId(), 'articleId' => $article->getId())
    //             ));
    //         }
    //     }

    //     return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Article:edit.html.twig', array(
    //         'association' => $association,
    //         'form'        => $form->createView(),
    //         'activities'  => $articleManager->getActivities($article),
    //     ));
    // }

    // /**
    //  * Return article form
    //  *
    //  * @param Article $article
    //  *
    //  * @return \Symfony\Component\Form\Form
    //  */
    // protected function getForm(Article $article)
    // {
    //     if (null === $article->getId()) {
    //         $action = $this->generateUrl(
    //             'open_miam_miam.admin.association.article.create',
    //             array('id' => $article->getAssociation()->getId())
    //         );
    //     } else {
    //         $action = $this->generateUrl(
    //             'open_miam_miam.admin.association.article.edit',
    //             array('id' => $article->getAssociation()->getId(), 'articleId' => $article->getId())
    //         );
    //     }

    //     return $this->createForm(
    //         $this->get('open_miam_miam.form.type.association_article'),
    //         $article,
    //         array('action' => $action, 'method' => 'POST')
    //     );
    // }

    // /**
    //  * Delete article
    //  *
    //  * @ParamConverter("article", class="IsicsOpenMiamMiamBundle:Article", options={"mapping": {"articleId": "id"}})
    //  *
    //  * @param Association $association
    //  * @param Article     $article
    //  *
    //  * @return Response
    //  */
    // public function deleteAction(Association $association, Article $article)
    // {
    //     $this->secure($association);
    //     $this->secureArticle($association, $article);

    //     $articleManager = $this->get('open_miam_miam.article_manager');
    //     $articleManager->delete($article);

    //     $this->get('session')->getFlashBag()->add('notice', 'admin.association.articles.message.deleted');

    //     return $this->redirect($this->generateUrl(
    //         'open_miam_miam.admin.association.article.list',
    //         array('id' => $association->getId())
    //     ));
    // }
}
