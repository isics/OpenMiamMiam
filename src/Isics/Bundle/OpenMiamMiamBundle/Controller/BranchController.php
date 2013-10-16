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

use Isics\Bundle\OpenMiamMiamBundle\Entity\Branch;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BranchController extends Controller
{
    /**
     * Shows branch homepage
     *
     * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branchSlug": "slug"}})
     *
     * @param Branch $branch
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showHomepageAction(Branch $branch)
    {
        return $this->render('IsicsOpenMiamMiamBundle:Branch:showHomepage.html.twig', array(
            'branch' => $branch,
        ));
    }

    /**
     * Shows branch presentation
     *
     * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branchSlug": "slug"}})
     *
     * @param Branch $branch
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showPresentationAction(Branch $branch)
    {
        return $this->render('IsicsOpenMiamMiamBundle:Branch:showPresentation.html.twig', array(
            'branch' => $branch,
        ));
    }

    /**
     * Lists all articles
     *
     * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branchSlug": "slug"}})
     *
     * @param Branch  $branch
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listArticlesAction(Branch $branch)
    {
        $articles = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Article')
            ->findPublishedForBranch($branch);

        return $this->render('IsicsOpenMiamMiamBundle:Branch:listArticles.html.twig', array(
            'branch'   => $branch,
            'articles' => $articles,
        ));
    }

    /**
     * Shows latest articles
     *
     * @param Branch  $branch
     * @param integer $limit
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showLatestArticlesAction(Branch $branch, $limit = 3)
    {
        $articles = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Article')
            ->findPublishedForBranch($branch, $limit);

        return $this->render('IsicsOpenMiamMiamBundle:Branch:showLatestArticles.html.twig', array(
            'branch'   => $branch,
            'articles' => $articles,
        ));
    }

    /**
     * Shows article
     *
     * @ParamConverter("branch", class="IsicsOpenMiamMiamBundle:Branch", options={"mapping": {"branchSlug": "slug"}})
     *
     * @param Branch  $branch      Branch
     * @param string  $articleSlug Article slug
     * @param integer $articleId   Article id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showArticleAction(Branch $branch, $articleSlug, $articleId)
    {
        $repository = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Article');

        $article = $repository->findOnePublishedByIdAndBranch($articleId, $branch);

        if (null === $article) {
            throw new NotFoundHttpException('Article not found');
        }

        if ($article->getSlug() !== $articleSlug) {
            return $this->redirect($this->generateUrl(
                'open_miam_miam.branch.article.show',
                array(
                    'branchSlug'  => $branch->getSlug(),
                    'articleSlug' => $article->getSlug(),
                    'articleId'   => $articleId,
                )
            ), 301);
        }

        $otherArticles = $repository->findPublishedForBranchExcept($branch, $article, 5);

        return $this->render('IsicsOpenMiamMiamBundle:Branch:showArticle.html.twig', array(
            'branch'        => $branch,
            'article'       => $article,
            'otherArticles' => $otherArticles,
        ));
    }

    /**
     * Shows next occurrences
     *
     * @param Branch  $branch
     * @param integer $limit
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showNextOccurrencesAction(Branch $branch, $limit = 5)
    {
        $branchOccurrences = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:BranchOccurrence')
            ->findAllNextForBranch($branch, false, $limit);

        return $this->render('IsicsOpenMiamMiamBundle:Branch:showNextOccurrences.html.twig', array(
            'branchOccurrences' => $branchOccurrences,
        ));
    }

    /**
     * Shows random producers
     *
     * @param Branch  $branch
     * @param integer $limit
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showRandomProducersAction(Branch $branch, $limit = 5)
    {
        $producers = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Producer')
            ->findAllRandomForBranch($branch, $limit);

        return $this->render('IsicsOpenMiamMiamBundle:Branch:showRandomProducers.html.twig', array(
            'producers' => $producers,
            'branch' => $branch
        ));
    }

    public function listProducersAction($branchSlug)
    {
        $branch = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Branch')->findOneBySlug($branchSlug);

        if (null === $branch) {
            throw new NotFoundHttpException('Branch not found');
        }

        $producers = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Producer')->findAllproducer($branch);

        if (null === $producers) {
            throw new NotFoundHttpException('Producers not found');
        }

        return $this->render('IsicsOpenMiamMiamBundle:Branch:showProducers.html.twig', array('producers'  => $producers, 'branch' => $branch));
    }
}
