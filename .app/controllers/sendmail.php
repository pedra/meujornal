<?php

//Se a variável 'data' existir...
if(isset($_POST['data'])){
    //Pegando os dados enviados da tela de cadastro
    $dt = json_decode($_POST['data']);
    $mail = trim(strip_tags($dt->mail));

    //Verificando se o e-mail já foi cadastrado.
    Q::db()->query('SELECT ID
                      FROM user
                      WHERE (SELECT ID FROM user WHERE EMAIL = :mail)
                      OR    (SELECT ID FROM user_tmp WHERE EMAIL = :mail)',[':mail'=>$mail]);
    if(Q::db()->result() !== false) exit(json_encode(['ok'=>'exist']));

    //Gravando na tabela de usuários temporários (até o cadastro)
    $key = (new Lib\Can)->encode((microtime(true)*100000));
    Q::db()->query('INSERT INTO user_tmp (TOKEN,EMAIL,IDATE)
                                  VALUES (:tk,:mail,:idate)',
                                  [':tk'=>$key, ':mail'=>$mail, ':idate'=>date('Y-m-d H:I:s')]);

    //Preparando o EMAIL
    $headers =
"MIME-Version: 1.1 \n".
"Content-type: text/html; charset=utf-8 \n".
"From: editor@meu.jor.br\n".
"Reply-To: editor@meu.jor.br\n";

    //Conteúdo do EMAIL
    $html = '<h1><b>meu</b>jornal</h1>
<p>Para concluir o cadastro, click no link abaixo ou copie e cole em seu navegador</p>
<a style="margin:20px 10px" href="'.URL.'u/'.$key.'">'.URL.'u/'.$key.'</a>
<p>Este e-mail será <b>excluído</b> de nossa base de dados em <b>24 horas</b>.<br/>Asseguramos que <b>nunca será usado para outros fins</b>.</p>
<br>
<p><i>Caso não tenha solicitado este cadastramento, por favor desconsidere este e-mail e receba nossas sinceras desculpas pelo transtorno.</i></p>
<br>
<p><b><big>Bill Rocha</big></b><br>Diretor & Editor Geral</p>
<hr>
<i>E-mail automático: não responda!</i>';

    //Enviando EMAIL
    @mail($mail, 'Cadastro no MEUjornal', $html, $headers, "-reditor@meu.jor.br");

    //Respondendo ao usuário do site
    exit(json_encode(['ok'=>'ok']));
}

//Se não, vai para a Capa
Q::go();