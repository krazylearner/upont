<?php

namespace KI\PonthubBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class SoftwaresController extends PonthubFileController
{
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $this->initialize('Software', 'Ponthub');
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Liste les logiciels",
     *  output="KI\PonthubBundle\Entity\Software",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Ponthub"
     * )
     */
    public function getSoftwaresAction() { return $this->getAll(); }

    /**
     * @ApiDoc(
     *  description="Retourne un logiciel",
     *  output="KI\PonthubBundle\Entity\Software",
     *  statusCodes={
     *   200="Requête traitée avec succès",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Ponthub"
     * )
     */
    public function getSoftwareAction($slug) { return $this->getOne($slug); }

    /**
     * @ApiDoc(
     *  description="Modifie un jeu",
     *  input="KI\PonthubBundle\Form\SoftwareType",
     *  statusCodes={
     *   204="Requête traitée avec succès mais pas d’information à renvoyer",
     *   400="La syntaxe de la requête est erronée",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Ponthub"
     * )
     */
    public function patchSoftwareAction($slug)
    {
        return $this->patch($slug, $this->get('security.context')->isGranted('ROLE_JARDINIER'));
    }

    /**
     * @ApiDoc(
     *  description="Télécharge un fichier sur Ponthub, et log le téléchargement",
     *  statusCodes={
     *   200="Requête traitée avec succès mais pas d’information à renvoyer",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Ponthub"
     * )
     * @Route\Get("/softwares/{slug}/download")
     */
    public function downloadSoftwareAction($slug)
    {
        $item = $this->getOne($slug);
        return $this->download($item);
    }
}
