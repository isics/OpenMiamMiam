<?php

/*
 * This file is part of the OpenMiamMiam project.
 *
 * (c) Isics <contact@isics.fr>
 *
 * This source file is subject to the AGPL v3 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Isics\Bundle\OpenMiamMiamBundle\Controller;

use Isics\Bundle\OpenMiamMiamBundle\Entity\ProducerAttendance;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Producer;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\BranchRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GeneralController extends Controller
{
    /**
     * Shows general homepage
     */
    public function showHomepageAction()
    {
        $branchesWithNbProducers = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Branch')->findWithProducersCount();

        return $this->render('IsicsOpenMiamMiamBundle::showHomepage.html.twig', array(
            'branchesWithNbProducers' => $branchesWithNbProducers,
        ));
    }

    /**
     * Lists all articles
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listArticlesAction()
    {
        $articles = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Article')
            ->findGeneralPublished();

        return $this->render('IsicsOpenMiamMiamBundle::listArticles.html.twig', array(
            'articles' => $articles,
        ));
    }

    /**
     * Shows latest articles
     *
     * @param integer $limit
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showLatestArticlesAction($limit = 3)
    {
        $articles = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Article')
            ->findGeneralPublished($limit);

        return $this->render('IsicsOpenMiamMiamBundle::showLatestArticles.html.twig', array(
            'articles' => $articles,
        ));
    }

    /**
     * Shows article
     *
     * @param string  $articleSlug Article slug
     * @param integer $articleId   Article id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showArticleAction($articleSlug, $articleId)
    {
        $repository = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Article');

        $article = $repository->findOneGeneralPublishedById($articleId);

        if (null === $article) {
            throw new NotFoundHttpException('Article not found');
        }

        if ($article->getSlug() !== $articleSlug) {
            return $this->redirect($this->generateUrl(
                'open_miam_miam.article.show',
                array(
                    'articleSlug' => $article->getSlug(),
                    'articleId'   => $articleId,
                )
            ), 301);
        }

        $otherArticles = $repository->findGeneralPublishedExcept($article, 5);

        return $this->render('IsicsOpenMiamMiamBundle::showArticle.html.twig', array(
            'article'       => $article,
            'otherArticles' => $otherArticles,
        ));
    }

    /**
     * Shows producer infos
     *
     * @ParamConverter("producer", class="IsicsOpenMiamMiamBundle:Producer", options={"mapping": {"producerSlug": "slug"}})
     */
    public function showProducerAction(Producer $producer)
    {
        $nextAttendancesOf = $this->get('open_miam_miam.producer_attendances_manager')->getNextAttendancesOf($producer);

        if (null === $nextAttendancesOf) {
            throw new NotFoundHttpException('Attendances not found');
        }

        return $this->render('IsicsOpenMiamMiamBundle::showProducer.html.twig', array('producer' => $producer,'nextAttendancesOf' => $nextAttendancesOf));
    }
}