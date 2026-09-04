<?php

namespace GlpiPlugin\Mostservicedesk;

use Session;
use Ticket;

class Visibility
{
    public static function addDefaultWhere(array $input): array
    {
        [$itemtype, $criteria] = $input;

        if ($itemtype !== Ticket::class || self::canBypassDepartmentFilter()) {
            return [$itemtype, $criteria];
        }

        $departmentIds = self::getAllowedDepartmentIds();
        if ($departmentIds === []) {
            return [$itemtype, $criteria];
        }

        $ids = implode(',', array_map('intval', $departmentIds));
        $criteria[] = new \QueryExpression(
            'EXISTS (SELECT 1'
            . ' FROM glpi_plugin_mostservicedesk_tickets AS msd_ticket_department'
            . ' WHERE msd_ticket_department.tickets_id = glpi_tickets.id'
            . ' AND msd_ticket_department.plugin_mostservicedesk_departments_id IN (' . $ids . '))'
        );

        return [$itemtype, $criteria];
    }

    public static function itemCan(Ticket $ticket): void
    {
        global $DB;

        if (
            self::canBypassDepartmentFilter()
            || !is_int($ticket->right)
            || $ticket->right <= 0
        ) {
            return;
        }

        $departmentIds = self::getAllowedDepartmentIds();
        if ($departmentIds === []) {
            return;
        }

        $allowed = $DB->request([
            'COUNT' => 'count',
            'FROM'  => TicketDepartment::getTable(),
            'WHERE' => [
                'tickets_id' => (int) $ticket->getID(),
                'plugin_mostservicedesk_departments_id' => $departmentIds,
            ],
        ])->current();

        if ((int) ($allowed['count'] ?? 0) === 0) {
            $ticket->right = 0;
        }
    }

    private static function canBypassDepartmentFilter(): bool
    {
        return Session::haveRight('config', UPDATE);
    }

    private static function getAllowedDepartmentIds(): array
    {
        global $DB;

        $userId = Session::getLoginUserID();
        if ($userId === false) {
            return [];
        }

        $ids = [];
        $iterator = $DB->request([
            'SELECT' => 'plugin_mostservicedesk_departments_id',
            'FROM'   => DepartmentUser::getTable(),
            'WHERE'  => [
                'users_id' => (int) $userId,
                'is_active' => 1,
            ],
        ]);

        foreach ($iterator as $row) {
            $ids[] = (int) $row['plugin_mostservicedesk_departments_id'];
        }

        return array_values(array_unique(array_filter($ids)));
    }
}
