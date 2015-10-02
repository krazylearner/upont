<?php
namespace KI\PonthubBundle\Selector;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use KI\PonthubBundle\Transformer\StringToGenresTransformer;

class GenresSelector extends AbstractType
{
    protected $transformer;

    public function __construct(StringToGenresTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'genres_selector';
    }
}
