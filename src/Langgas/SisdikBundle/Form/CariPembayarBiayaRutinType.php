<?php

namespace Langgas\SisdikBundle\Form;

use Doctrine\ORM\EntityRepository;
use Langgas\SisdikBundle\Entity\Sekolah;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;
use JMS\DiExtraBundle\Annotation\FormType;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;

/**
 * @FormType
 */
class CariPembayarBiayaRutinType extends AbstractType
{
    /**
     * @var SecurityContext
     */
    private $securityContext;

    /**
     * @InjectParams({
     *     "securityContext" = @Inject("security.context")
     * })
     *
     * @param SecurityContext $securityContext
     */
    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @return Sekolah
     */
    private function getSekolah()
    {
        return $this->securityContext->getToken()->getUser()->getSekolah();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sekolah = $this->getSekolah();

        $builder
            ->add('tahun', 'entity', [
                'class' => 'LanggasSisdikBundle:Tahun',
                'label' => 'label.year.entry',
                'multiple' => false,
                'expanded' => false,
                'property' => 'tahun',
                'empty_value' => 'label.selectyear',
                'required' => false,
                'query_builder' => function (EntityRepository $repository) use ($sekolah) {
                    $qb = $repository->createQueryBuilder('tahun')
                        ->where('tahun.sekolah = :sekolah')
                        ->orderBy('tahun.tahun', 'DESC')
                        ->setParameter('sekolah', $sekolah)
                    ;

                    return $qb;
                },
                'attr' => [
                    'class' => 'small',
                ],
                'label_render' => false,
                'horizontal' => false,
            ])
            ->add('searchkey', null, [
                'required' => false,
                'attr' => [
                    'class' => 'medium search-query',
                    'placeholder' => 'label.searchkey',
                ],
                'label_render' => false,
                'horizontal' => false,
            ])
            ->add('adaTunggakan', 'checkbox', [
                'required' => false,
                'attr' => [
                    'class' => 'menunggak-bayar',
                ],
                'label_render' => true,
                'label' => 'label.ada.tunggakan',
                'widget_checkbox_label' => 'widget',
                'horizontal' => false,
            ])
            ->add('batasanPencarianTunggakan', 'choice', [
                'multiple' => false,
                'expanded' => false,
                'empty_value' => 'label.pilih.batasan.pencarian.tunggakan',
                'required' => false,
                'label_render' => false,
                'horizontal' => false,
                'choices' => [
                    5 => '5.siswa',
                    10 => '10.siswa',
                    15 => '15.siswa',
                ],
                'preferred_choices' => [5],
            ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'csrf_protection' => false,
            ])
        ;
    }

    public function getName()
    {
        return 'sisdik_caripembayarbiayarutin';
    }
}
