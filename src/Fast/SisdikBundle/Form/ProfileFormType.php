<?php
namespace Fast\SisdikBundle\Form;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\SecurityExtraBundle\Security\Authorization\Expression\Expression;

class ProfileFormType extends BaseType
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    protected function buildUserForm(FormBuilderInterface $builder, array $options) {
        $securityContext = $this->container->get('security.context');
        $otherthanstudents = '';
        foreach ($this->container->getParameter('security.role_hierarchy.roles') as $keys => $values) {
            if ($keys == 'ROLE_SISWA' || $keys == 'ROLE_USER') {
                continue;
            }
            $otherthanstudents .= "'$keys', ";
        }
        $otherthanstudents = preg_replace('/, $/', '', $otherthanstudents);

        if ($securityContext
                ->isGranted(
                        array(
                            new Expression("hasAnyRole($otherthanstudents)")
                        ))) {
            $builder
                    ->add('username', null,
                            array(
                                'required' => true
                            ));
        }
        $builder
                ->add('email', 'email',
                        array(
                            'required' => true
                        ))
                ->add('name', null,
                        array(
                            'required' => true, 'label' => 'label.name.full'
                        ));

        if ($securityContext->isGranted('ROLE_SUPER_ADMIN')) {
            foreach ($this->container->getParameter('security.role_hierarchy.roles') as $keys => $values) {
                if ($keys == 'ROLE_SUPER_ADMIN') {
                    if ($this->container->get('security.context')
                            ->isGranted('ROLE_SUPER_ADMIN')) {
                        $roles[$keys] = str_replace('_', ' ', $keys);
                    } else {
                        break;
                    }
                }
                $roles[$keys] = str_replace('_', ' ', $keys);
            }
            $builder
                    ->add('roles', 'choice',
                            array(
                                    'choices' => $roles, 'label' => 'label.roles',
                                    'multiple' => true, 'expanded' => true,
                            ));
        } else if ($securityContext->isGranted('ROLE_ADMIN')) {
            foreach ($this->container->getParameter('security.role_hierarchy.roles') as $keys => $values) {
                if ($keys == 'ROLE_SUPER_ADMIN') {
                    if ($this->container->get('security.context')
                            ->isGranted('ROLE_SUPER_ADMIN')) {
                        $roles[$keys] = str_replace('_', ' ', $keys);
                    } else {
                        break;
                    }
                }
                if ($keys == 'ROLE_SISWA') {
                    continue;
                }
                $roles[$keys] = str_replace('_', ' ', $keys);
            }
            $builder
                    ->add('roles', 'choice',
                            array(
                                    'choices' => $roles, 'label' => 'label.roles',
                                    'multiple' => true, 'expanded' => true,
                            ));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver
                ->setDefaults(
                        array(
                            'data_class' => 'Fast\SisdikBundle\Entity\User',
                        ));
    }

    public function getName() {
        return 'fast_sisdikbundle_profile';
    }
}
