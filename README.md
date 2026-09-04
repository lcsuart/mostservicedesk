# MOST Service Desk

Plugin para GLPI 11 que adiciona departamentos responsáveis por tickets sem multiplicar entidades.

## Modelo de autorização

- Entidade GLPI: loja/origem.
- Departamento do plugin: área responsável.
- Login x departamento: autorização adicional.
- Visibilidade final: entidade permitida **e** departamento permitido.

## Localização no GLPI

`Configuração/Manutenção > Listas suspensas > Gestão de Tickets - Departamentos`

## Versão 0.1.0

- Instalador com três tabelas próprias.
- Cadastro de departamentos.
- Associação de usuários aos departamentos.
- Configurações iniciais de WhatsApp e anexos.
- Dados preservados na desinstalação durante a fase de homologação.

O filtro de tickets e os endpoints do n8n serão adicionados na próxima etapa após validação desta estrutura no GLPI 11.0.8.
