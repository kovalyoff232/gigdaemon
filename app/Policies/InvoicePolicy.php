<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    private function isOwner(User $user, Invoice $invoice): bool
    {
        return $user->id === $invoice->user_id;
    }
    
    public function view(?User $user, Invoice $invoice): bool { return $user && $user->id === $invoice->user_id; }
    public function update(User $user, Invoice $invoice): bool { return $this->isOwner($user, $invoice); }
    public function delete(User $user, Invoice $invoice): bool { return $this->isOwner($user, $invoice); }
	
}