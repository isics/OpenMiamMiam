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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Article;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Association;
use Isics\Bundle\OpenMiamMiamBundle\Form\Type\AssociationArticleType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class ArticleController extends BaseController
{
    /**
     * Secures article for association
     *
     * @param Association $association
     * @param Article $article
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function secureArticle(Association $association, Article $article)
    {
        if ($association->getId() !== $article->getAssociation()->getId()) {
            throw $this->createNotFoundException('Invalid article for association');
        }
    }

    /**
     * List articles
     *
     * @param Association $association
     *
     * @return Response
     */
    public function listAction(Association $association)
    {
        $this->secure($association);

        $articles = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Article')->findForAssociation($association);

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Article:list.html.twig', array(
            'association' => $association,
            'articles'    => $articles
        ));
    }

    /**
     * Create article
     *
     * @param Request     $request
     * @param Association $association
     *
     * @return Response
     */
    public function createAction(Request $request, Association $association)
    {
        $this->secure($association);

        $articleManager = $this->get('open_miam_miam.article_manager');
        $article = $articleManager->createForAssociation($association);

        $form = $this->getForm($article);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $articleManager->save($article, $this->get('security.token_storage')->getToken()->getUser());

                $this->get('session')->getFlashBag()->add('notice', 'admin.association.articles.message.created');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.article.edit',
                    array('id' => $association->getId(), 'articleId' => $article->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Article:create.html.twig', array(
            'association' => $association,
            'form'        => $form->createView(),
        ));
    }

    /**
     * Edit article
     *
     * @ParamConverter("article", class="IsicsOpenMiamMiamBundle:Article", options={"mapping": {"articleId": "id"}})
     *
     * @param Request     $request
     * @param Association $association
     * @param Article     $article
     *
     * @return Response
     */
    public function editAction(Request $request, Association $association, Article $article)
    {
        $this->secure($association);
        $this->secureArticle($association, $article);

        $articleManager = $this->get('open_miam_miam.article_manager');

        $form = $this->getForm($article);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $articleManager->save($article, $this->get('security.token_storage')->getToken()->getUser());

                $this->get('session')->getFlashBag()->add('notice', 'admin.association.articles.message.updated');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.association.article.edit',
                    array('id' => $association->getId(), 'articleId' => $article->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Association\Article:edit.html.twig', array(
            'association' => $association,
            'form'        => $form->createView(),
            'activities'  => $articleManager->getActivities($article),
        ));
    }

    /**
     * Return article form
     *
     * @param Article $article
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function getForm(Article $article)
    {
        if (null === $article->getId()) {
            $action = $this->generateUrl(
                'open_miam_miam.admin.association.article.create',
                array('id' => $article->getAssociation()->getId())
            );
        } else {
            $action = $this->generateUrl(
                'open_miam_miam.admin.association.article.edit',
                array('id' => $article->getAssociation()->getId(), 'articleId' => $article->getId())
            );
        }

        return $this->container->get('form.factory')
            ->createNamedBuilder(
                'open_miam_miam_association_article',
                AssociationArticleType::class,
                $article,
                array('action' => $action, 'method' => 'POST')
            )
            ->getForm();
    }

    /**
     * Delete article
     *
     * @ParamConverter("article", class="IsicsOpenMiamMiamBundle:Article", options={"mapping": {"articleId": "id"}})
     *
     * @param Association $association
     * @param Article     $article
     *
     * @return Response
     */
    public function deleteAction(Association $association, Article $article)
    {
        $this->secure($association);
        $this->secureArticle($association, $article);

        $articleManager = $this->get('open_miam_miam.article_manager');
        $articleManager->delete($article);

        $this->get('session')->getFlashBag()->add('notice', 'admin.association.articles.message.deleted');

        return $this->redirect($this->generateUrl(
            'open_miam_miam.admin.association.article.list',
            array('id' => $association->getId())
        ));
    }
}
