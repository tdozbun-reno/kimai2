<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventSubscriber\Actions;

use App\Entity\Invoice;
use App\Event\PageActionsEvent;

class InvoiceSubscriber extends AbstractActionsSubscriber
{
    public static function getActionName(): string
    {
        return 'invoice';
    }

    public function onActions(PageActionsEvent $event): void
    {
        $payload = $event->getPayload();

        /** @var Invoice $invoice */
        $invoice = $payload['invoice'];

        if ($invoice->getId() === null) {
            return;
        }

        if (!$invoice->isPending()) {
            $event->addAction('invoice.pending', ['url' => $this->path('admin_invoice_status', ['id' => $invoice->getId(), 'status' => 'pending'])]);
        } else {
            $event->addAction('invoice.paid', ['url' => $this->path('admin_invoice_status', ['id' => $invoice->getId(), 'status' => 'paid']), 'class' => 'modal-ajax-form']);
        }

        $allowDelete = $this->isGranted('delete_invoice');
        if (!$invoice->isCanceled()) {
            $id = $allowDelete ? 'invoice.cancel' : 'trash';
            $event->addAction($id, ['url' => $this->path('admin_invoice_status', ['id' => $invoice->getId(), 'status' => 'canceled']), 'title' => 'invoice.cancel', 'translation_domain' => 'actions']);
        }

        $event->addDivider();

        $event->addAction('download', ['url' => $this->path('admin_invoice_download', ['id' => $invoice->getId()]), 'target' => '_blank']);

        if ($this->isGranted('delete_invoice')) {
            $event->addDelete($this->path('admin_invoice_delete', ['id' => $invoice->getId(), 'token' => $payload['token']]), false);
        }
    }
}
