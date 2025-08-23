<?php
declare(strict_types=1);

namespace Brille24\SyliusCustomerOptionsPlugin\Form\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\String\Slugger\SluggerInterface;

final class GenerateCustomerOptionCodeSubscriber implements EventSubscriberInterface {

    public function __construct(
        private SluggerInterface $slugger,
        private string $defaultLocale
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [FormEvents::PRE_SUBMIT => 'preSubmit'];
    }

    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        if (!\is_array($data)) {
            return;
        }

        if (!empty($data['code'])) {
            return; // Don't overwrite existing code
        }

        $name = $this->extractNameFromTranslations($data['translations'] ?? []);
        if ($name === null) {
            return;
        }

        $slug = \mb_strtolower($this->slugger->slug($name)->toString());
        $data['code'] = \str_replace('-', '_', $slug);

        $event->setData($data);
    }

    private function extractNameFromTranslations(array $translations): ?string
    {
        if (isset($translations[$this->defaultLocale]['name'])) {
            $name = $translations[$this->defaultLocale]['name'];
            if (\is_string($name) && \trim($name) !== '') {
                return $name;
            }
        }

        foreach ($translations as $trans) {
            if (isset($trans['name']) && \trim((string)$trans['name']) !== '') {
                return (string)$trans['name'];
            }
        }

        return null;
    }
}
