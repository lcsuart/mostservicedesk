<?php

namespace GlpiPlugin\Mostservicedesk;

use Dropdown;
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

        $selected = 0;
        if ((int) $ticket->getID() > 0) {
            $relation = $DB->request([
                'SELECT' => 'plugin_mostservicedesk_departments_id',
                'FROM'   => TicketDepartment::getTable(),
                'WHERE'  => ['tickets_id' => (int) $ticket->getID()],
                'LIMIT'  => 1,
            ])->current();

            $selected = (int) ($relation['plugin_mostservicedesk_departments_id'] ?? 0);
        }

        echo '<section class="accordion-item" aria-label="Departamento responsável">';
        echo '<h2 class="accordion-header" id="mostservicedesk-department-heading">';
        echo '<button class="accordion-button" type="button" data-bs-toggle="collapse" ';
        echo 'data-bs-target="#mostservicedesk-department-content" aria-expanded="true" ';
        echo 'aria-controls="mostservicedesk-department-content">';
        echo '<i class="ti ti-building-community me-1"></i>';
        echo '<span class="item-title">Departamento responsável</span>';
        echo '</button></h2>';
        echo '<div id="mostservicedesk-department-content" class="accordion-collapse collapse show" ';
        echo 'aria-labelledby="mostservicedesk-department-heading">';
        echo '<div class="accordion-body">';
        echo '<label class="form-label" for="' . self::FIELD_NAME . '">';
        echo 'Selecione o departamento que receberá o chamado ';
        echo '<span class="text-danger" aria-label="obrigatório">*</span>';
        echo '</label>';

        if ($departments === []) {
            echo '<div class="alert alert-warning mb-0">';
            echo 'Nenhum departamento ativo para abertura de chamados.';
            echo '</div>';
        } else {
            Dropdown::showFromArray(self::FIELD_NAME, $departments, [
                'value' => $selected,
                'display_emptychoice' => true,
                'emptylabel' => 'Selecione um departamento',
                'width' => '100%',
            ]);
        }

        echo '</div></div></section>';
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
