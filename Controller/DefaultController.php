<?php

namespace Bangpound\Bundle\ElasticsearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DefaultController
 * @package Bangpound\Bundle\ElasticsearchBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @param $name
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('BangpoundElasticsearchBundle:Default:index.html.twig', array('name' => $name));
    }
}
