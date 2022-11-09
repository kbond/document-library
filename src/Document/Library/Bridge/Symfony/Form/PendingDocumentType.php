<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zenstruck\Document\PendingDocument;

/**
 * @author Jakub Caban <kuba.iluvatar@gmail.com>
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PendingDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            eventName: FormEvents::PRE_SUBMIT,
            listener: static function(FormEvent $event) use ($options) {
                if (!$formData = $event->getData()) {
                    return;
                }

                if (!$options['multiple']) {
                    if ($formData instanceof File) {
                        $event->setData(new PendingDocument($formData));
                    }

                    return;
                }

                $data = [];

                foreach ($formData as $file) {
                    if ($file instanceof File) {
                        $data[] = new PendingDocument($file);
                    }
                }

                $event->setData($data);
            },
            priority: -10
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => fn(Options $options) => $options['multiple'] ? null : PendingDocument::class,
        ]);
    }

    public function getParent(): string
    {
        return FileType::class;
    }
}
