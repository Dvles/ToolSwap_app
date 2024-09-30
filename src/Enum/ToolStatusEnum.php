<?php


namespace App\Enum;

enum ToolStatusEnum: string
{
    case PENDING = 'pending';      // Borrow request is made but not yet approved
    case APPROVED = 'approved';    // Request approved, but tool not yet collected
    case ACTIVE = 'active';        // Tool is with the borrower, in use
    case RETURNED = 'returned';    // Tool returned but not confirmed received by owner
    case COMPLETED = 'completed';  // Tool returned and confirmed by owner
    case CANCELED = 'canceled';    // Borrow request canceled before handover
}

