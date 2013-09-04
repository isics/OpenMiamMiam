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
use Isics\Bundle\OpenMiamMiamBundle\Entity\Category;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CatalogController extends Controller
{
    /**
     * Shows Categories
     *
     * @param Branch $branch
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCategoriesAction(Branch $branch)
    {
        $categories = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Category')
            ->findAllAvailableInBranch($branch);

        return $this->render('IsicsOpenMiamMiamBundle:Catalog:showCategories.html.twig', array(
            'branch'     => $branch,
            'categories' => $categories,
        ));
    }

    /**
     * Shows a category with its products
     *
     * @ParamConverter("branch",   class="IsicsOpenMiamMiamBundle:Branch",   options={"mapping": {"branch_slug":   "slug"}})
     * @ParamConverter("category", class="IsicsOpenMiamMiamBundle:Category", options={"mapping": {"category_slug": "slug"}})
     *
     * @param Branch   $branch
     * @param Category $category
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showCategoryAction(Branch $branch, Category $category)
    {
        $products = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Product')
            ->findAllVisibleInBranchAndCategory($branch, $category);

        if (0 === count($products)) {
            throw $this->createNotFoundException('No product was found in this category.');
        }

        return $this->render('IsicsOpenMiamMiamBundle:Catalog:showCategory.html.twig', array(
            'branch'   => $branch,
            'category' => $category,
            'products' => $products,
        ));
    }

    /**
     * Shows product details
     *
     * @ParamConverter("branch",   class="IsicsOpenMiamMiamBundle:Branch",   options={"mapping": {"branch_slug":   "slug"}})
     * @ParamConverter("category", class="IsicsOpenMiamMiamBundle:Category", options={"mapping": {"category_slug": "slug"}})
     *
     * @param Branch   $branch
     * @param Category $category
     * @param string   $product_slug
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showProductAction(Branch $branch, Category $category, $product_slug)
    {
        $product = $this->getDoctrine()->getRepository('IsicsOpenMiamMiamBundle:Product')
            ->findOneBySlugAndVisibleInBranch($product_slug, $branch);

        if (null === $product || $category !== $product->getCategory()) {
            throw new NotFoundHttpException('Product not found');
        }

        return $this->render('IsicsOpenMiamMiamBundle:Catalog:showProduct.html.twig', array(
            'branch'   => $branch,
            'category' => $category,
            'product'  => $product,
        ));
    }
}
