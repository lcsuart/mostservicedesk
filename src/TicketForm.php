<?php

namespace GlpiPlugin\Mostservicedesk;

use Glpi\Application\View\TemplateRenderer;
use Session;
use Ticket;

class TicketForm
{
    private const FIELD_NAME = '_mostservicedesk_department_id';

    public static function showDepartmentSection(array $params): void
    {
        global $DB;

        $ticket = $params['item'] ?? null;
        if (!$ticket instanceof Ticket) {
            return;
        }

        $departments = [];
        $iterator = $DB->request([
            'SELECT' => ['id', 'name'],
            'FROM'   => Department::getTable(),
            'WHERE'  => [
                'is_active' => 1,
                'tickets_enabled' => 1,
            ],
            'ORDER' => ['name'],
        ]);

        foreach ($iterator as $row) {
            $departments[(int) $row['id']] = (string) $row['name'];
        }

        $selected = (int) (
            $params['options'][self::FIELD_NAME]
            ?? $ticket->input[self::FIELD_NAME]
            ?? $_POST[self::FIELD_NAME]
            ?? $_GET[self::FIELD_NAME]
            ?? 0
        );

        if ($selected === 0 && (int) $ticket->getID() > 0) {
            $relation = $DB->request([
                'SELECT' => 'plugin_mostservicedesk_departments_id',
                'FROM'   => TicketDepartment::getTable(),
                'WHERE'  => ['tickets_id' => (int) $ticket->getID()],
                'LIMIT'  => 1,
            ])->current();

            $selected = (int) ($relation['plugin_mostservicedesk_departments_id'] ?? 0);
        }

        $choices = [0 => 'Selecione um departamento'] + $departments;

        echo TemplateRenderer::getInstance()->renderFromStringTemplate(
            <<<'TWIG'
{% import 'components/form/fields_macros.html.twig' as fields %}
{{ fields.dropdownArrayField(
    field_name,
    selected,
    departments,
    'Departamento',
    {
        required: true,
        full_width: true,
        width: '100%'
    }
) }}
TWIG,
            [
                'field_name' => self::FIELD_NAME,
                'selected' => $selected,
                'departments' => $choices,
            ]
        );
    }

    public static function preItemAdd(Ticket $ticket): void
    {
        self::validateInput($ticket);
    }

    public static function itemAdd(Ticket $ticket): void
    {
        self::persistDepartment($ticket);
    }

    public static function preItemUpdate(Ticket $ticket): void
    {
        if (array_key_exists(self::FIELD_NAME, $ticket->input)) {
            self::validateInput($ticket);
        }
    }

    public static function itemUpdate(Ticket $ticket): void
    {
        if (array_key_exists(self::FIELD_NAME, $ticket->input)) {
            self::persistDepartment($ticket);
        }
    }

    private static function validateInput(Ticket $ticket): void
    {
        global $DB;

        $departmentId = (int) ($ticket->input[self::FIELD_NAME] ?? 0);
        $validCount = 0;

        if ($departmentId > 0) {
            $result = $DB->request([
                'COUNT' => 'count',
                'FROM'  => Department::getTable(),
                'WHERE' => [
                    'id' => $departmentId,
                    'is_active' => 1,
                    'tickets_enabled' => 1,
                ],
            ])->current();
            $validCount = (int) ($result['count'] ?? 0);
        }

        if ($validCount === 0) {
            Session::addMessageAfterRedirect(
                'Selecione um departamento responsável válido.',
                false,
                ERROR
            );
            $ticket->input = false;
        }
    }

    private static function persistDepartment(Ticket $ticket): void
    {
        global $DB;

        if (!is_array($ticket->input)) {
            return;
        }

        $departmentId = (int) ($ticket->input[self::FIELD_NAME] ?? 0);
        $ticketId = (int) $ticket->getID();
        if ($departmentId <= 0 || $ticketId <= 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $existing = $DB->request([
            'SELECT' => 'id',
            'FROM'   => TicketDepartment::getTable(),
            'WHERE'  => ['tickets_id' => $ticketId],
            'LIMIT'  => 1,
        ])->current();

        if (isset($existing['id'])) {
            $DB->update(
                TicketDepartment::getTable(),
                [
                    'plugin_mostservicedesk_departments_id' => $departmentId,
                    'date_mod' => $now,
                ],
                ['id' => (int) $existing['id']]
            );
            return;
        }

        $DB->insert(TicketDepartment::getTable(), [
            'tickets_id' => $ticketId,
            'plugin_mostservicedesk_departments_id' => $departmentId,
            'date_creation' => $now,
            'date_mod' => $now,
        ]);
    }
}
