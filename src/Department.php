<?php

namespace GlpiPlugin\Mostservicedesk;

use CommonDropdown;
use Dropdown;
use Html;
use Session;

class Department extends CommonDropdown
{
    public static $rightname = 'config';

    public static function canCreate(): bool
    {
        return static::canUpdate();
    }

    public static function getTypeName($nb = 0): string
    {
        return $nb > 1 ? 'Departamentos de tickets' : 'Departamento de tickets';
    }

    public function showForm($ID, array $options = []): bool
    {
        if (!Session::haveRight('config', UPDATE)) {
            return false;
        }

        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo '<tr class="tab_bg_1"><td>Nome</td><td>';
        echo Html::input('name', ['value' => $this->fields['name'] ?? '', 'size' => 50]);
        echo '</td><td>Ativo</td><td>';
        Dropdown::showYesNo('is_active', (int) ($this->fields['is_active'] ?? 1));
        echo '</td></tr>';

        echo '<tr class="tab_bg_1"><td>Gerencia tickets</td><td>';
        Dropdown::showYesNo('tickets_enabled', (int) ($this->fields['tickets_enabled'] ?? 1));
        echo '</td><td>Disponível no WhatsApp</td><td>';
        Dropdown::showYesNo('whatsapp_enabled', (int) ($this->fields['whatsapp_enabled'] ?? 1));
        echo '</td></tr>';

        echo '<tr class="tab_bg_1"><td>Permitir áudio</td><td>';
        Dropdown::showYesNo('allow_audio', (int) ($this->fields['allow_audio'] ?? 1));
        echo '</td><td>Permitir vídeo</td><td>';
        Dropdown::showYesNo('allow_video', (int) ($this->fields['allow_video'] ?? 1));
        echo '</td></tr>';

        echo '<tr class="tab_bg_1"><td>Permitir documentos</td><td>';
        Dropdown::showYesNo('allow_documents', (int) ($this->fields['allow_documents'] ?? 1));
        echo '</td><td>Categoria padrão</td><td>';
        Dropdown::show('ITILCategory', [
            'name'  => 'default_category_id',
            'value' => (int) ($this->fields['default_category_id'] ?? 0),
        ]);
        echo '</td></tr>';

        echo '<tr class="tab_bg_1"><td>Mensagem inicial</td><td colspan="3">';
        echo Html::textarea([
            'name'  => 'welcome_message',
            'value' => $this->fields['welcome_message'] ?? '',
            'cols'  => 100,
            'rows'  => 5,
        ]);
        echo '</td></tr>';

        $this->showFormButtons($options);

        if ($ID > 0) {
            DepartmentUser::showForDepartment((int) $ID);
        }
        return true;
    }
}
