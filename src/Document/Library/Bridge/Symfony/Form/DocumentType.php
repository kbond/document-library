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
use Zenstruck\Document;
use Zenstruck\Document\Library;
use Zenstruck\Document\LibraryRegistry;
use Zenstruck\Document\Namer;
use Zenstruck\Document\PendingDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentType extends AbstractType
{
    public function __construct(private ?LibraryRegistry $registry = null, private ?Namer $namer = null)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(
            eventName: FormEvents::PRE_SUBMIT,
            listener: function(FormEvent $event) use ($options) {
                if (!$formData = $event->getData()) {
                    return;
                }

                if (!$options['multiple']) {
                    if ($formData instanceof File) {
                        $event->setData($this->store($options, $formData));
                    }

                    return;
                }

                $data = [];

                foreach ($formData as $file) {
                    if ($file instanceof File) {
                        $data[] = $this->store($options, $file);
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
            'library' => null,
            'namer' => 'expression',
            'data_class' => fn(Options $options) => $options['multiple'] ? null : Document::class,
            'namer_context' => [],
        ]);

        $resolver->setAllowedTypes('namer_context', 'array');

        $libraryTypes = [Library::class];

        if ($this->registry) {
            $libraryTypes[] = 'string';
        }

        $resolver->setAllowedTypes('library', $libraryTypes);
        $resolver->setAllowedTypes('namer', [Namer::class, 'string', \Stringable::class]);

        $resolver->setRequired('library');
        $resolver->setRequired('namer');
    }

    public function getParent(): string
    {
        return FileType::class;
    }

    private function store(array $options, File $file): Document
    {
        $library = $options['library'];
        $namer = $options['namer'];
        $context = $options['namer_context'];

        if (\is_string($namer)) {
            $context['namer'] = $namer;
        }

        $document = new PendingDocument($file);
        $namer = $namer instanceof Namer ? $namer : $this->namer ??= new Namer\MultiNamer();
        $library = $library instanceof Library ? $library : $this->registry?->get($library) ?? throw new \LogicException();

        return $library->store($namer->generateName($document, $context), $document);
    }
}
