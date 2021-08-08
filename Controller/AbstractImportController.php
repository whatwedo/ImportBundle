<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: mauri
 * Date: 10.05.18
 * Time: 12:27.
 */

namespace whatwedo\ImportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use whatwedo\ImportBundle\Form\ImportType;
use whatwedo\ImportBundle\Manager\ImportManager;
use whatwedo\ImportBundle\Model\Import;

abstract class AbstractImportController extends AbstractController
{
    protected ImportManager $importManager;

    public function __construct(ImportManager $importManager)
    {
        $this->importManager = $importManager;
    }

    protected function importData(Request $request, $class, $formView, $resultView): Response
    {
        $importDto = new Import();

        $form =  $this->createForm(
            ImportType::class,
            $importDto
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $result = $this->importManager->importData($importDto, $class);

            return $this->renderForm($resultView, [
                'form' => $form,
                'definitionBuilder' =>  $this->importManager->getDefinitionBuilder(
                    $this->importManager->getDefinition($class)
                ),
                'result' =>  $result,
            ]);
        }

        return $this->renderForm($formView, [
            'form' => $form,
            'definitionBuilder' =>  $this->importManager->getDefinitionBuilder(
                $this->importManager->getDefinition($class)
            ),
        ]);
    }

}
