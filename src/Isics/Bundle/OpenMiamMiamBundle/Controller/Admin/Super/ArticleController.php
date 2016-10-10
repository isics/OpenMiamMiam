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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Article;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ArticleController extends Controller
{
    /**
     * List articles
     *
     * @return Response
     */
    public function listAction()
    {
        $articles = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Article')->findForSuper();

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Article:list.html.twig', array(
            'articles' => $articles
        ));
    }

    /**
     * Create article
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $articleManager = $this->get('open_miam_miam.article_manager');
        $article = $articleManager->createForSuper();

        $form = $this->getForm($article);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $articleManager->save($article, $this->get('security.token_storage')->getToken()->getUser());

                $this->get('session')->getFlashBag()->add('notice', 'admin.super.articles.message.created');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.super.article.edit',
                    array('articleId' => $article->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Article:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Edit article
     *
     * @ParamConverter("article", class="IsicsOpenMiamMiamBundle:Article", options={"mapping": {"articleId": "id"}})
     *
     * @param Request $request
     * @param Article $article
     *
     * @return Response
     */
    public function editAction(Request $request, Article $article)
    {
        $articleManager = $this->get('open_miam_miam.article_manager');

        $form = $this->getForm($article);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $articleManager->save($article, $this->get('security.token_storage')->getToken()->getUser());

                $this->get('session')->getFlashBag()->add('notice', 'admin.super.articles.message.updated');

                return $this->redirect($this->generateUrl(
                    'open_miam_miam.admin.super.article.edit',
                    array('articleId' => $article->getId())
                ));
            }
        }

        return $this->render('IsicsOpenMiamMiamBundle:Admin\Super\Article:edit.html.twig', array(
            'form'       => $form->createView(),
            'activities' => $articleManager->getActivities($article),
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
                'open_miam_miam.admin.super.article.create'
            );
        } else {
            $action = $this->generateUrl(
                'open_miam_miam.admin.super.article.edit',
                array('articleId' => $article->getId())
            );
        }

        return $this->createForm(
            $this->get('open_miam_miam.form.type.super_article'),
            $article,
            array('action' => $action, 'method' => 'POST')
        );
    }

    /**
     * Delete article
     *
     * @ParamConverter("article", class="IsicsOpenMiamMiamBundle:Article", options={"mapping": {"articleId": "id"}})
     *
     * @param Article $article
     *
     * @return Response
     */
    public function deleteAction(Article $article)
    {
        $articleManager = $this->get('open_miam_miam.article_manager');
        $articleManager->delete($article);

        $this->get('session')->getFlashBag()->add('notice', 'admin.super.articles.message.deleted');

        return $this->redirect($this->generateUrl('open_miam_miam.admin.super.article.list'));
    }
}
