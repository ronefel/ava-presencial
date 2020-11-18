<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * As strings de plug-in são definidas aqui.
 *
 * @package     mod_videotime
 * @category    string
 * @copyright   2018 bdecent gmbh <https://bdecent.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/videotime/lib.php');

$string['activity_name'] = 'Nome da atividade';
$string['activity_name_help'] = 'Nome exibido no curso para este módulo de atividade Vimeo Play';
$string['albums'] = 'Álbuns';
$string['api_not_authenticated'] = 'A API do Vimeo não é autenticada.';
$string['api_not_configured'] = 'API do Vimeo não está configurada.';
$string['apply'] = 'Aplicar';
$string['authenticated'] = 'Autenticado';
$string['authenticate_vimeo'] = 'Autenticar com o Vimeo';
$string['authenticate_vimeo_success'] = 'Autenticação Vimeo bem-sucedida. Agora você pode usar recursos que dependem da API do Vimeo.';
$string['choose_video'] = 'Escolher Vídeo';
$string['client_id'] = 'ID do cliente Vimeo';
$string['client_id_help'] = 'O ID do cliente é gerado quando você cria um "Aplicativo" na sua conta do Vimeo. Vá para https://developer.vimeo.com/apps/new para iniciar esse processo.';
$string['client_secret'] = 'Segredo do cliente Vimeo';
$string['client_secret_help'] = 'O segredo do cliente é gerado quando você cria um "aplicativo" na sua conta do Vimeo. Vá para https://developer.vimeo.com/apps/new para iniciar esse processo.';
$string['completion_on_finish'] = 'Conclusão no final do vídeo';
$string['completion_on_percent'] = 'Porcentagem de conclusão sobre o tempo do vídeo';
$string['completion_on_view'] = 'Conclusão no tempo de visualização';
$string['default'] = 'Padrão';
$string['discover_videos'] = 'Descubra vídeos do Vimeo';
$string['discovering_videos'] = 'Descobrindo {$a->count} vídeos';
$string['duration'] = 'Duração';
$string['embed_options'] = 'Opções de incorporação';
$string['embed_options_defaults'] = 'Opções de incorporação padrão';
$string['embeds'] = 'Incorpora';
$string['force'] = 'Forçar';
$string['force_help'] = 'Se marcado, esse padrão substituirá a configuração da instância.';
$string['gradeitemnotcreatedyet'] = 'Um item do livro de notas não existe para esta atividade. Marque <b> Definir nota igual para visualizar a porcentagem </b> acima, salve e edite esta atividade novamente para definir a categoria e a nota aprovada.';
$string['invalid_session_state'] = 'Estado de sessão inválido.';
$string['label_mode'] = 'Exibir na página do curso';
$string['label_mode_help'] = 'Incorpore o vídeo na página principal do curso, semelhante à atividade Rótulo. Isso desativa a Reprodução automática.';
$string['modulename'] = 'Vimeo Play';
$string['modulenameplural'] = 'Instâncias do Vimeo Play';
$string['needs_authentication'] = 'Precisa de nova autenticação';
$string['next_activity'] = 'Próxima atividade';
$string['next_activity_auto'] = 'Ir automaticamente para a próxima atividade';
$string['next_activity_auto_help'] = 'Carregue automaticamente a próxima atividade quando o aluno concluir o vídeo.';
$string['next_activity_in_course'] = 'Padrão: Próxima Atividade em Curso';
$string['next_activity_button'] = 'Ativar Botão Próxima Atividade';
$string['next_activity_button_help'] = 'Exiba um botão acima do vídeo que vincula à próxima atividade que o usuário deve concluir.';
$string['not_authenticated'] = 'Não autenticado';
$string['option_autoplay'] = 'Reprodução automática';
$string['option_autoplay_help'] = 'Iniciar automaticamente a reprodução do vídeo. Observe que isso não funcionará em alguns dispositivos ou navegadores que o bloqueiam.';
$string['option_byline'] = 'Assinatura';
$string['option_byline_help'] = 'Mostre a assinatura no vídeo.';
$string['option_color'] = 'Cor';
$string['option_color_help'] = 'Especifique a cor dos controles de vídeo. As cores podem ser substituídas pelas configurações de incorporação do vídeo.';
$string['option_height'] = 'Altura';
$string['option_height_help'] = 'A altura exata do vídeo. O padrão é a altura da maior versão disponível do vídeo.';
$string['option_maxheight'] = 'Altura máxima';
$string['option_maxheight_help'] = 'Igual à altura, mas o vídeo não excederá o tamanho nativo do vídeo.';
$string['option_maxwidth'] = 'Largura máxima';
$string['option_maxwidth_help'] = 'Igual à largura, mas o vídeo não excederá o tamanho nativo do vídeo.';
$string['option_muted'] = 'Silenciado';
$string['option_muted_help'] = 'Silencie este vídeo em carregamento. Necessário para reprodução automática em determinados navegadores.';
$string['option_playsinline'] = 'Reproduz em linha';
$string['option_playsinline_help'] = 'Reproduzir vídeo em linha em dispositivos móveis, para entrar automaticamente em tela cheia na reprodução, deixe este campo desmarcado.';
$string['option_portrait'] = 'Retrato';
$string['option_portrait_help'] = 'Mostre o retrato no vídeo.';
$string['option_responsive'] = 'Responsivo';
$string['option_responsive_help'] = 'Se marcado, o player de vídeo responderá e se adaptará ao tamanho da página ou da tela.';
$string['option_speed'] = 'Velocidade';
$string['option_speed_help'] = 'Mostre os controles de velocidade no menu de preferências e ative a API da taxa de reprodução (disponível para contas PRO e Business).';
$string['option_title'] = 'Título';
$string['option_title_help'] = 'Mostrar o título do vídeo.';
$string['option_transparent'] = 'Transparente';
$string['option_transparent_help'] = 'O player responsivo e o plano de fundo transparente são ativados por padrão, para desativar, defina esse parâmetro como false.';
$string['option_width'] = 'Largura';
$string['option_width_help'] = 'A largura exata do vídeo. O padrão é a largura da maior versão disponível do vídeo.';
$string['option_forced'] = '{$a->option} é globalmente forçado a: {$a->value}';
$string['modulename_help'] = 'A atividade Vimeo Play permite que o professor
<ul>
    <li>incorpore facilmente vídeos do Vimeo, apenas adicionando a URL</li>
    <li>adicione conteúdo acima e abaixo do player de vídeo</li>
</ul>
';
$string['pluginname'] = 'Vimeo Play';
$string['pluginadministration'] = 'Administração do Vimeo Play';
$string['preview_image'] = 'Imagem de pré-visualização';
$string['preview_image_help'] = 'Imagem exibida para o usuário.';
$string['process_videos'] = 'Processar vídeos';
$string['rate_limit'] = 'Vimeo API request limit';
$string['resume_playback'] = 'Retomar a reprodução';
$string['resume_playback_help'] = 'Reinicie automaticamente o vídeo quando o usuário retornar à atividade. A reprodução começa onde o usuário parou.';
$string['estimated_request_time'] = 'Tempo estimado restante';
$string['search_help'] = 'Pesquisar nome, descrição, álbuns, tags ...';
$string['seconds'] = 'Segundos';
$string['session_not_found'] = 'Sessão do usuário não encontrada.';
$string['settings'] = 'Vimeo Play configurações';
$string['showdescription'] = 'Descrição de exibição';
$string['showdescription_help'] = 'A descrição é exibida acima do vídeo e pode ser mostrada na página do curso.';
$string['state'] = 'Estado';
$string['state_finished'] = 'Concluído';
$string['state_help'] = 'O usuário terminou o vídeo?';
$string['state_incomplete'] = 'Incompleto';
$string['status'] = 'Status';
$string['store_pictures'] = 'Armazenar miniaturas';
$string['store_pictures_help'] = 'Se ativada, as miniaturas do Vimeo serão armazenadas localmente. Caso contrário, as imagens serão entregues a partir do Vimeo externamente.';
$string['subplugintype_videotimeplugin'] = 'Vimeo Play Plugin';
$string['subplugintype_videotimeplugin_plural'] = 'Vimeo Play Plugins';
$string['upgrade_vimeo_account'] = 'AVISO: considere atualizar sua conta do Vimeo. Seu limite de solicitações de API é muito baixo.';
$string['use'] = 'Usar';
$string['viewpercentgrade'] = 'Defina a nota igual à porcentagem de exibição.';
$string['viewpercentgrade_help'] = 'Crie um item de nota para este vídeo. O aluno receberá uma nota igual à porcentagem de visualização do vídeo.';
$string['views'] = 'Visualizações';
$string['views_help'] = 'Número de vezes que a atividade foi visualizada.';
$string['view_report'] = 'Ver relatório';
$string['videotime:view_report'] = 'Ver relatório (Pro only)';
$string['video_description_intro'] = 'Descrição do vídeo';
$string['video_description'] = 'Nota de rodapé do vídeo';
$string['video_description_help'] = 'É exibido abaixo do vídeo.';
$string['videotime:addinstance'] = 'Adicione um novo módulo Vimeo Play';
$string['videotime:view'] = 'Ver vídeo do Vimeo';
$string['vimeo_url'] = 'Vimeo URL';
$string['vimeo_url_help'] = 'URL completa do vídeo do Vimeo.';
$string['vimeo_url_invalid'] = 'A URL do Vimeo é inválida. Copie diretamente do navegador da web.';
$string['vimeo_url_missing'] = 'A URL do Vimeo não está definida.';
$string['watch_time'] = 'Tempo de exibição';
$string['watch_time_help'] = 'Quanto tempo o aluno assistiu ao vídeo no total (em etapas de 5s).';
$string['watch_percent'] = 'Porcentagem assistido';
$string['watch_percent_help'] = 'O momento mais distante do vídeo que o aluno assistiu.';
