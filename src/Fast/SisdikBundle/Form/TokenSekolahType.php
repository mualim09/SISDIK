<?php
namespace Fast\SisdikBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation\FormType;

/**
 * @FormType
 */
class TokenSekolahType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $querybuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('s')
            ->from('FastSisdikBundle:Sekolah', 's')
            ->orderBy('s.nama', 'ASC')
        ;

        $builder
            ->add('mesinProxy')
            ->add('sekolah', 'entity', [
                'class' => 'FastSisdikBundle:Sekolah',
                'label' => 'label.school',
                'multiple' => false,
                'expanded' => false,
                'property' => 'nama',
                'empty_value' => false,
                'required' => true,
                'query_builder' => $querybuilder,
            ]);
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'Fast\SisdikBundle\Entity\TokenSekolah',
            ])
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'sisdik_tokensekolah';
    }
}