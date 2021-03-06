<?php

namespace KI\PonthubBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SoftwaresController extends PonthubFileController
{
    public function setContainer(ContainerInterface $container = null)
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
     * @Route("/softwares")
     * @Method("GET")
     */
    public function getSoftwaresAction()
    {
        return $this->getAll();
    }

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
     * @Route("/softwares/{slug}")
     * @Method("GET")
     */
    public function getSoftwareAction($slug)
    {
        $software = $this->getOne($slug);

        return $this->json($software);
    }

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
     * @Route("/softwares/{slug}")
     * @Method("PATCH")
     */
    public function patchSoftwareAction($slug)
    {
        $data = $this->patch($slug, $this->is('JARDINIER'));

        return $this->formJson($data);
    }

    /**
     * @ApiDoc(
     *  description="Supprime un logiciel",
     *  statusCodes={
     *   204="Requête traitée avec succès mais pas d’information à renvoyer",
     *   401="Une authentification est nécessaire pour effectuer cette action",
     *   403="Pas les droits suffisants pour effectuer cette action",
     *   404="Ressource non trouvée",
     *   503="Service temporairement indisponible ou en maintenance",
     *  },
     *  section="Publications"
     * )
     * @Route("/softwares/{slug}")
     * @Method("DELETE")
     */
    public function deleteSoftwareAction($slug)
    {
        $this->delete($slug, $this->is('JARDINIER'));

        return $this->json(null, 204);
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
     * @Route("/softwares/{slug}/download")
     * @Method("GET")
     */
    public function downloadSoftwareAction($slug)
    {
        $item = $this->getOne($slug, !$this->is('EXTERIEUR'));
        return $this->download($item);
    }
}
