<?php
// This file is NOT part of Moodle - http://moodle.org/
// This is a non-core contributed module.
//
// This is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License
// can be see at <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'enrol_dbextended', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   enrol_dbextended
 * @copyright  2012 Luis Alcantara, based on code from Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['createcourseonloginuserenrolment'] = 'Criar os novos cursos pertencentes as matrículas dos usuários, ao fazer login';
$string['createcourseonloginuserenrolment_desc'] = 'Habilita a criação local automática de cursos aos quais o aluno está matriculado através do banco de dados externo, cujos dados sejam suficientes a sua criação no Moodle. <br />Utilize esta opção APENAS com sistemas com um pequeno número de matrículas por usuário.';
$string['createdcourseforcehidden'] = 'Forçar a criação de cursos escondidos para estudantes';
$string['createdcourseforcehidden_desc'] = 'Selecione se deseja forçar que os cursos criados a partir deste método de inscrições sejam escondidos independentemente das configurações do curso modelo ou das configurações padrão.';
$string['created_from_dbextended'] = 'Criado com Banco de Dados Externo';
$string['dbencoding'] = 'Codificação do banco de dados';
$string['dbhost'] = 'Nome ou número IP do servidor';
$string['dbhost_desc'] = 'Tipo do servidor do Banco de Dados ou hostname';
$string['dbname'] = 'Nome do banco de dados';
$string['dbpass'] = 'Senha do banco de dados';
$string['dbsetupsql'] = 'Linha de comando do banco de dados.';
$string['dbsetupsql_desc'] = 'Comando SQL para configuração especial de banco de dados, geralmente usado para configurar a codificação da comunicação - por exemplo para o MySQL e PostgreSQL: <em> SET NAMES \'utf8\' </ em>';
$string['dbsybasequoting'] = 'Usar notação Sybase';
$string['dbsybasequoting_desc'] = 'Estilo de aspas sybase - necessário no Oracle, MS SQL e alguns bancos de dados. Não use no MySQL!';
$string['dbtype'] = 'Driver do banco de dados';
$string['dbtype_desc'] = 'Nome do driver de base de dados ADOdb, tipo da base de dados externa.';
$string['dbuser'] = 'Usuário do banco de dados';
$string['debugdb'] = 'Debug ADOdb';
$string['debugdb_desc'] = 'Depurar a conexão do ADOdb à base de dados externa - usar quando retornada uma página em branco durante o login. Não é adequado para ambientes de produção!';
$string['defaultcategory'] = 'Categoria padrão de cursos novos';
$string['defaultcategory_desc'] = 'Categoria padrão para cursos auto-criados. Usado quando não é especificada a categoria ou a mesma não é encontrada.';
$string['defaultrole'] = 'Papel padrão';
$string['defaultrole_desc'] = 'A função que será atribuída por padrão, se nenhuma outra for especificada na tabela externa.';
$string['extremovedaction'] = 'Ação ao encontrar remoção de matrícula remota';
$string['extremovedaction_help'] = 'Selecionar o que deve ser feito quando a inscrição de usuários desaparece de uma fonte externa de inscrições. Note que alguns dados de usuários e configurações são apagadas do curso quando é cancelada a matrícula.';
$string['ignorehiddencourses'] = 'Ignorar cursos ocultos';
$string['ignorehiddencourses_desc'] = 'Se habilitado, os usuários não  serão inscritos em cursos que estão definidos como não disponíveis para os alunos.';
$string['localcoursecategoryfield'] = 'O nome do campo na tabela das categorias de cursos que nós estamos usando para comparar com entradas do banco de dados remoto (p.ex. idnumber)';
$string['localcoursefield'] = 'O nome do campo na tabela do curso que nós estamos usando para comparar com entradas do banco de dados remoto (p.ex. idnumber)';
$string['localrolefield'] = 'O nome do campo na tabela de funções que nós estamos usando para comparar com entradas no banco de dados remoto. (p.ex. shortname)';
$string['localuserfield'] = 'O nome do campo na tabela de usuários que nós estamos usando para comparar com entradas do banco de dados remoto (p.ex. idnumber).';
$string['maxcreatecourseonlogin'] = 'Número máximo de cursos a criar, por login de usuário';
$string['maxcreatecourseonlogin_desc'] = 'Define o número máximo de cursos a criar, por login de usuário. Se o usuário estiver atribuído externamente a mais novos cursos do que o limite estipulado nesta configuração, estes novos cursos restantes serão criados no próximo login deste usuário. Isto previne processos de login muito demorados e erros de login por demora excessiva.';
// the next two came out of order due to cyclic referencing
$string['newcoursecategory'] = 'Campo de identificação da categoria do novo curso';
$string['newcoursecategoryname'] = 'Campo de nome da nova categoria de cursos';
$string['newcoursecategories_desc'] = 'Permite a reprodução da hierarquia de categorias de cursos no Moodle, se todos os campos abaixo estiverem corretamente configurados.<br />
    O campo \''.$string['newcoursecategory'].'\' será utilizado como lista de identificadores externos das categorias dos cursos, e todas as categorias de cursos retornadas serão criadas se não estiverem disponíveis localmente.<br />
    O número de nomes e identificadores separados nos campos \''.$string['newcoursecategory'].'\' e \''.$string['newcoursecategoryname'].'\' deverá ser exatamente igual.
    Cada identificador de categorias de cursos na hierarquia DEVE SER ÚNICO, caso contrário a categoria será encontrada e utilizada como a categoria de curso destino do referido curso.';
$string['newcoursecategory_desc'] = 'O campo de identificação da categoria do novo curso será utilizado para a localização da categoria de cursos ao qual destina-se o curso a ser criado.<br />
    Caso a categoria de curso apontada não seja encontrada, o curso será criado sob a \''.$string['defaultcategory'].'\'.<br />
    Por fim, se for utilizada a criação de categorias de cursos, as categorias de cursos não encontradas através deste identificador, serão automaticamente criadas.';
$string['newcoursecategoryhierachyseparator'] = 'Um caractere especial para separação de nomes das categorias de cursos';
$string['newcoursecategoryhierachyseparator_desc'] = 'A special character that could be used under course categories hierarchy reproduction, applied on name splitting if more than one category level retrieved.';
$string['newcoursecategoryhierachyseparator_notapplied'] = 'não aplicado';
$string['newcoursecategoryidnumber'] = 'Campo de identificação da nova categoria de cursos';
$string['newcoursecategoryidnumber_desc'] = 'O campo identificador ou a lista de identificação das Categorias de Cursos. Se a lista de identificação for utilizada, será necessário selecionar uma opção no campo '.$string['newcoursecategoryhierachyseparator'].'.';
$string['newcoursecategoryname_desc'] = 'O campo de nome ou a lista de nomes das Categorias de Cursos. Se a lista de nomes for utilizada, será necessário selecionar uma opção no campo '.$string['newcoursecategoryhierachyseparator'].'.';
$string['newcoursefullname'] = 'Campo nome completo do novo curso';
$string['newcourseidnumber'] = 'Campo de identificação do número do novo curso';
$string['newcourseshortname'] = 'Campo de nome curto do novo curso';
$string['newcoursetable'] = 'Tabela de novos cursos remotos';
$string['newcoursetable_desc'] = 'Especifique o nome da tabela que contém a lista dos cursos que devem ser criados automaticamente. Se estiver vazio, significa não criar cursos.';
$string['newcoursemodality'] = 'Tabela remota de modalidade de curso';
$string['newcoursemodality_desc'] = '';
$string['newcoursetype'] = 'Tabela remota de tipo de curso';
$string['newcoursetype_desc'] = '';
$string['newcoursestartdate'] = 'Campo de data inicial do curso';
$string['newcoursestartdate_desc'] = 'Especifique o nome do campo que contém a data inicial do curso';
$string['newcourseenddate'] = 'Campo de data final do curso';
$string['newcourseenddate_desc'] = 'Especifique o nome do campo que contém a data final do curso';

$string['newcourselimitdaten1'] = 'Campo de data limite para N1';
$string['newcourselimitdaten1_desc'] = 'Especifique o nome do campo que contém a data limite para informar a nota N1';
$string['newcourselimitdaten2'] = 'Campo de data limite para N2';
$string['newcourselimitdaten2_desc'] = 'Especifique o nome do campo que contém a data limite para informar a nota N2';
$string['newcourselimitdaten3'] = 'Campo de data limite para N3';
$string['newcourselimitdaten3_desc'] = 'Especifique o nome do campo que contém a data limite para informar a nota N3';

$string['pluginname'] = 'Banco de dados externo estendido';
$string['pluginname_desc'] = 'Você pode usar um banco de dados externo (de quase todo tipo) para controlar a sua matrícula. Presume-se que o seu banco de dados externo contém pelo menos um campo com a identificação do curso, e outro com a identificação de usuário. Estes serão comparados com os campos que você escolher do curso e as tabelas de usuário locais.';
$string['remotecoursefield'] = 'O nome do campo na tabela remota que nós estamos usando para comparar com entradas na tabela curso.';
$string['remotecoursefield_desc'] = 'O nome do campo na tabela remota que estamos usando para associar com as entradas na tabela do curso.';
$string['remoteenroltable'] = 'Tabela de inscrição de usuários remotos ';
$string['remoteenroltable_desc'] = 'Especifique o nome da tabela que contém a lista de usuários a serem inscritos. Se vazio significa que não há inscrições de usuários para sincronizar.';
$string['remoterolefield'] = 'O nome do campo na tabela remota que nós estamos usando para comparar com entradas na tabela funções.';
$string['remoterolefield_desc'] = 'O nome do campo na tabela remota que estamos usando para associar com as entradas na tabela de funções.';
$string['remoterafield'] = 'Campo de RA';
$string['remoterafield_desc'] = 'Campo usado para armazenar o Registro Acadêmico que será vinculado à matrícula';
$string['remoteuserfield'] = 'Campo "user" remoto';
$string['remotegroupfield_desc'] = 'Campo da tabela remota que é usado para colocar um usuário num grupo';
$string['remotegroupfield'] = 'Campo "Grupo" remoto';
$string['settingsheaderdb'] = 'Conexão externa do banco de dados';
$string['settingsheaderlocal'] = 'Mapeamento de campo Local';
$string['settingsheaderremote'] = 'Sincronização de inscrição remota ';
$string['settingsheadernewcourses'] = 'Criação de novos cursos';
$string['settingsheadernewcoursecategories'] = 'Criação de novas categorias de curso';
$string['remoteuserfield_desc'] = 'O nome do campo na tabela remota que nós estamos usando para comparar com entradas na tabela usuários.';
$string['templatecourse'] = 'Novo modelo de curso';
$string['templatecourseead'] = 'Novo modelo de curso - EaD';
$string['templatecourse_desc'] = 'Opcional: cursos auto-criados podem copiar as configurações de um curso modelo. Digite aqui o nome abreviado do curso modelo.';
$string['templatecoursepos'] = 'Novo modelo de curso - Pós';
$string['welcometocoursetext'] = 'Olá, {$shortname}! Bem vindo ao curso {$coursename}! Se você ainda não fez, você deve editar o seu Perfil de Usuário para que possamos conhecer você melhor: {$profileurl}';
$string['welcometocoursehtml'] = '<p>Olá, {$username}!</p><p>Bem vindo ao curso <strong>{$coursename}!</strong><p/><p> Se você ainda não o fez, edite o seu Perfil de Usuário para que possamos conhecer você melhor: {$profileurl}</p><p>Ótimos estudos!</p>';
$string['welcometocoursehtmltext'] = 'HTML da mensagem de boas-vindas do curso';
$string['welcometocoursehtmldesc'] = 'Mensagem que será mostrada ao estudante quando o usuário é inscrito em um curso';
$string['enablewelcomeemail'] = 'Habilitar e-mail de boas-vindas';
$string['remoteturmadiscfield'] = 'Campo de Turma/Disciplina';
$string['remoteturmadiscfield_desc'] = 'Campo usado para armazenar a turma/disciplina nas matrículas importadas.';