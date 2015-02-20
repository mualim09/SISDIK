<?php

namespace Langgas\SisdikBundle\Form;

use Langgas\SisdikBundle\Entity\Sekolah;
use Langgas\SisdikBundle\Entity\User;
use Langgas\SisdikBundle\Form\EventListener\SekolahSubscriber;
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
class TransaksiPembayaranRutinType extends AbstractType
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
     * @return User
     */
    private function getUser()
    {
        return $this->securityContext->getToken()->getUser();
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
        $user = $this->getUser();

        $builder->addEventSubscriber(new SekolahSubscriber($sekolah));

        $builder
            ->add('sekolah', 'sisdik_entityhidden', [
                'required' => true,
                'class' => 'LanggasSisdikBundle:Sekolah',
                'data' => $sekolah->getId(),
            ])
            ->add('dibuatOleh', 'sisdik_entityhidden', [
                'required' => true,
                'class' => 'LanggasSisdikBundle:User',
                'data' => $user->getId(),
            ])
            ->add('nominalPembayaran', 'money', [
                'currency' => 'IDR',
                'required' => true,
                'precision' => 0,
                'grouping' => 3,
                'attr' => [
                    'class' => 'medium pay-amount',
                    'autocomplete' => 'off',
                ],
                'label' => 'label.pay.amount',
            ])
            ->add('keterangan', 'text', [
                'required' => false,
                'attr' => [
                    'class' => 'xlarge',
                    'autocomplete' => 'off',
                ],
                'label' => 'label.payment.description',
            ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'Langgas\SisdikBundle\Entity\TransaksiPembayaranRutin',
            ])
        ;
    }

    public function getName()
    {
        return 'sisdik_transaksipembayaranrutin';
    }
}