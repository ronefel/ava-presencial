# # #
# Função de tratamento de post para o php
$.form = (post) ->

	# FUNÇÃO ENVIAR PARA PHP #
	$.form.send = (post) ->
		# # #
		# value = valores para conexao e busca no banco
		# # #

		# Função ajax
		$.ajax(
			type: "post"
			url: "php/index.php" #local no php
			cache: false
			data: post
			async: false
		)

		# resultado de retorno
		.done (data) -> 
			# console.log data # exibe valor de data
			post = data
			# console.log post
		# retorna post a solicitação com 'eval()'
		return $.parseJSON(post)
		# return post


	done = $.form.send {"ajax":post}

	return done

# # # # # # # # # # # # # # # # # #
# # # # # # # # # # # # # # # # # #

# # # #
#  Cria arquivo compilado do less

# # declara eestrutura do objeto
# $.style = {"result":"null","seletor":null,"ajax":{"action":{"content":{}, "type":null}, "license":{}}}

# # define seletor do estilo less compilado
# $.style.seletor = $('style')['0']

# # adiciona o contéúdo compilado do style
# $.style.ajax.action.content.less = $($($.style.seletor)).text()
# $.style.ajax.action.type = 'less to css'

# $.style.result = $.form $.style.ajax

# console.log $.style.result

#  Cria arquivo compilado do less
# # # #

# # # # # # # # # # # # # # # # # # # # #
# # Inicia tratamentos dos controles  # #

$.appCtrl = {} if !$.appCtrl

$.appCtrl.togo = {} if !$.appCtrl.togo

# Inicia globais de tratamentos
$.appCtrl = (post) ->
	#// Requer uma lista $('[data-ctrl]')
	temp = {'_proccess':{'_true':false, 'togo':{}, 'display':{}, 'apr':{}, 'atividade':{}, 'incorporar':{}}, '_erro':{'_true':false}, '_warning':{'_true':false}, '_done':{'_true':false}, 'appCtrl':{}}


	# # # # # #
	# inicia processo de post como falso
	temp._proccess.post = false

	# valida se post recebeu algo
	if post.length >= 1
		temp._proccess.post = true
	# # # # # #


	# # # # # #
	#== valida se o processo de post é valido
	if temp._proccess.post is true

		#== inicia loop para capiturar cada item do tipo ['data-ctrl']
		i = 0
		while i < post.length

			# adiciona content para cada instancia do post
			temp.appCtrl[i] = {}

			# reserva o html
			temp.appCtrl[i].this = $(post)[i]

			# reserva as configurações
			temp.appCtrl[i].app = $($(post)[i]).data().appCtrl

			#** valida se a solicitação de controle é para navegação
			if temp.appCtrl[i].app.togo

				# define processo togo atual falso
				temp._proccess.togo[i] = {}

				#// envia os parametros para a função
				temp._proccess.togo[i] = $.appCtrl.togo temp.appCtrl[i]

			#** valida se a solicitação é para exibir alguma caixa de dialogo
			if temp.appCtrl[i].app.display

				# define processo togo atual falso
				temp._proccess.display[i] = $.appCtrl.display temp.appCtrl[i]

			#** valida se a solicitação é para tratar a apresentação
			if temp.appCtrl[i].app.apr

				# declara processo atual
				temp._proccess.apr[i] = {}

				# define processo togo atual falso
				temp._proccess.apr[i] = $.appCtrl.apr temp.appCtrl[i]

			#** valida se a solicitação é para tratar as atividades
			if temp.appCtrl[i].app.atividade

				# declara processo atual
				temp._proccess.atividade[i] = {}

				# define processo togo atual falso
				temp._proccess.atividade[i] = $.appCtrl.atividade temp.appCtrl[i]

			#** valida se a solicitação é para tratar elementos SVG iterativo
			if temp.appCtrl[i].app.incorporar

				# declara processo atual
				temp._proccess.incorporar[i] = {}

				# define processo togo atual falso
				temp._proccess.incorporar[i] = $.appCtrl.incorporar temp.appCtrl[i]


			#// adiciona contador no loop
			i++

	# console.log temp

$.appCtrl.togo = (post) ->
	#// Requer um parametro array
	#// {"._.list":["togo"],"togo":{"._.//":"Parametros para navegação nas paginas","._.list":["to","cover"],"._.type":["array"],"to":{"._.//":"Vai até esse seletor do tipo #ID ou .class","._.type":["string"]},"cover":{"._.//":"Executa a ação de a partir da capa, true para exibir, false para ocultar","._.type":["boolean"]}}}

	#// Requer uma lista $('[data-ctrl]')
	temp = {'_proccess':{'_true':false, 'volume':{}, 'capa':{}, 'content':{}, 'seletor':{}, 'display':{}}, '_erro':{'_true':false}, '_warning':{'_true':false}, '_done':{'_true':false}}

	# ativa como botão de disparo
	$(post.this).click ->


		# Valida se a ação é para a capa
		if !post.app.togo.cover != undefined

			#// localiza apartir do clique o volume
			temp._proccess.volume = $(post.this).closest('.app-volume')

			#// localiza capa a partir de volume
			temp._proccess.capa = $(temp._proccess.volume).find('.app-cover')

			#// localiza contents a partir de volume
			temp._proccess.content = $(temp._proccess.volume).find('.app-contents')


			#== valida se é para entrar na capa
			if post.app.togo.cover is true

				# remove display
				temp._proccess.content.removeClass('app-display').queue (next) ->

					# adiciona no-display
					$(this).addClass('app-no-display').delay(1000).queue (next) ->

						# remove no-display
						$(this).removeClass('app-no-display')

						next() #// Fim da espera

					# remove no-display
					temp._proccess.capa.removeClass('app-no-display').queue (next) ->

						# adiciona display
						$(this).addClass('app-display')

						next() #// Fim da espera

					next() #// Fim da espera

			#== valida se é para entrar no conteúdo
			if post.app.togo.cover is false

				# remove display
				temp._proccess.capa.removeClass('app-display').queue (next) ->

					# adiciona no-display
					$(this).addClass('app-no-display').delay(1000).queue (next) ->

						# remove no-display
						$(this).removeClass('app-no-display')

						next() #// Fim da espera

					# remove no-display
					temp._proccess.content.removeClass('app-no-display').queue (next) ->

						# adiciona display
						$(this).addClass('app-display')

						$('.app-nav-section-item.app-secao').text('Sumário')
						$('.app-nav-section-item.app-marker').text('')
						$('.app-nav-section-item.app-titulo').text('')

						next() #// Fim da espera

					next() #// Fim da espera

		# Valida se a ação é para navegar
		if !post.app.togo.to != undefined

			#// reserva seletor
			temp._proccess.seletor = '#'+post.app.togo.to

			temp._proccess.display = $.appCtrl.section(temp._proccess.seletor, 'app-display')
			# temp._proccess.displayNo = $.appCtrl.section(temp._proccess.seletor, '.app-no-display')

			#== Trata se é a primeira pagina na estrutura
			if temp._proccess.display.position.page is 'first'

				# posiciona eq
				temp.eq = 0
				temp.eq = temp._proccess.display.item.eq if !temp._proccess.display.item.this is false

				$.appCtrl.sectionDisplay([
					[temp._proccess.display.page.this, 'in', 'on'],
					[$(temp._proccess.display.pattern).find(".section-page-item:eq(#{temp.eq})"), 'in', 'on']
				])


			#== Trata quando já tiver uma pagina ativa
			if temp._proccess.display.position.page is 'before' or temp._proccess.display.position.page is 'after'

				# posiciona eq
				temp.eq = 0
				temp.eq = temp._proccess.display.item.eq if !temp._proccess.display.item.this is false

				if temp.eq > 0
					$('.app-cap-section').removeClass('app-display')

				if $(temp._proccess.display.page.this).find(".section-page-item:eq(#{temp.eq})").data() != undefined
					temp.a = $(temp._proccess.display.page.this).find(".section-page-item:eq(#{temp.eq})").data().appCtrl.navigation.section
					temp.b = $(temp._proccess.display.page.this).find(".section-page-item:eq(#{temp.eq})").data().appCtrl.navigation.page
					temp.c = '•'
					if $(temp._proccess.display.page.this).find(".section-page-item:eq(#{temp.eq})").data().appCtrl.navigation.page is false
						temp.b = ''
						temp.c = ''

				else
					temp.a = 'Sumário'
					temp.b = ''
					temp.c = ''

				$('.app-nav-section-item.app-secao').text(temp.a)
				$('.app-nav-section-item.app-marker').text(temp.c)
				$('.app-nav-section-item.app-titulo').text(temp.b)

				delete(temp.a)
				delete(temp.b)
				delete(temp.c)

				$.appCtrl.sectionDisplay([
					[temp._proccess.display.it.page.this, 'out', temp._proccess.display.position.page],
					[temp._proccess.display.it.item.this, 'out', temp._proccess.display.position.page],
					[temp._proccess.display.page.this, 'in', temp._proccess.display.position.page],
					[$(temp._proccess.display.page.this).find(".section-page-item:eq(#{temp.eq})"), 'in', 'on']
				])
			#== Trata quando a pagina estiver posucionada
			if temp._proccess.display.position.page is 'this'

				#== Valida se item tem posição
				if temp._proccess.display.position.item is 'first'
					$.appCtrl.sectionDisplay([[$(temp._proccess.display.page.this).find(".section-page-item:eq(0)"), 'in', 'on']])

				#== valida se é antes ou depois
				if temp._proccess.display.position.item is 'before' or temp._proccess.display.position.item is 'after'
					$.appCtrl.sectionDisplay([
						[temp._proccess.display.it.item.this, 'out', temp._proccess.display.position.item],
						[temp._proccess.display.item.this, 'in', temp._proccess.display.position.item]
					])

				#== valida se é o item atual
				if temp._proccess.display.position.item is 'this'
					$.appCtrl.sectionDisplay([ [temp._proccess.display.item.this, 'in', 'on'] ])

			# console.log temp._proccess.display
	#// retorna a função
	return temp

$.appCtrl.sectionDisplay = (post) ->
	# # # #
	# {"post":{"0":{"._.//":"Recebe o valor referente ao seletor","._.required":true,"._.type":["string"]},"1":{"._.//":"Recebe um valor referente ao tipo de entrada do display \"display ou no-display\"","._.required":true,"._.type":["string"],"._.exacly":{"string":{"._.//":"\"out\" para \"app-no-display\", \"in\" para \"app-display\"","text":["out","in"]}}},"2":{"._.//":"Recebe a direção da animação","._.required":true,"._.type":["string"],"._.exacly":{"string":{"._.//":"\"before\" para antes e \"after\" para depois","text":["before","after"]}}},"._.required":true,"._.list":[0,"1",2],"._.type":["array"],"._.exacly":{"array":"numeric"}}}

	# inicia contagem
	i = 0

	# inicia laço para a função
	while i < post.length
		seletor = post[i][0]
		display = post[i][1]
		position = post[i][2]

		#== caso seja para o processo de saida do elemento
		if display is 'out'

			$(post[i][3]['this']).scrollTop(post[i][3]['position']) if post[i][3] != undefined

			#** remove os displays de outras paginas
			$(seletor).removeClass('app-display').addClass(position).queue (next) ->
					
				$(this).addClass('app-no-display').delay(700).queue (next) ->

					$(this).removeClass("app-no-display").delay(1000).queue (next) ->

						$(this).removeClass("on after before").queue (next) ->

							# $(this).scrollTop(0)

							next() #// Fim da espera

						next() #// Fim da espera

					next() #// Fim da espera

				next() #// Fim da espera

		#== caso seja para o processo de entrada do elemento
		if display is 'in'

			$(post[i][3]['this']).scrollTop(post[i][3]['position']) if post[i][3] != undefined

			#** remove os displays de outras paginas
			$(seletor).removeClass('app-no-display').addClass(position).delay(700).queue (next) ->

				$(this).addClass('app-display').delay(1000).queue (next) ->

					$(this).removeClass("on after before").queue (next) ->

						# $(this).scrollTop(0)

						next() #// Fim da espera

					next() #// Fim da espera

				next() #// Fim da espera

		# adiciona 1 no final
		i++

	#// apaga contador
	i = undefined

$.appCtrl.section = (id, display) ->

	temp = {'pattern':false, 'page':{'this':false ,'display':false}, 'item':{'this':false, 'display':false}, 'it':{'page':{'this':false}, 'item':{'this':false}}, 'position':{'page':false, 'item':false}}

	# localiza livro atual
	temp.pattern = $(id).closest('.app-volume')


	#== Localiza seção a partir do item e adiciona em this
	#** valida se o item é seção
	temp.page.this = $(id) if $(id).hasClass('section-page')
	temp.item.this = $(id) if $(id).hasClass('section-page-item')

	#** localiza a seção mais proxima
	if temp.page.this is false

		temp.page.this = $(id).closest('.section-page') if $(id).closest('.section-page').length
	
	if temp.item.this is false

		temp.item.this = $(id).closest('.section-page-item') if $(id).closest('.section-page-item').length

		# #** Resolve itens nao declarados e que passam pela mesma instancia
		# if !temp.page.this is false and temp.item.this is false

		# 	#// procura primeiro ativo de cima para baixo
		# 	temp.item.this = $(temp.page.this).find('.section-page-item.'+display) if $(temp.page.this).find('.section-page-item.'+display).length

		# 	#// procura primeiro ativo de baixo para cima
		# 	if !$(temp.page.this).find('.section-page-item.'+display).length
		# 		temp.item.this = $(temp.page.this).find('.section-page-item:eq(0)') if  $(temp.page.this).find('.section-page-item:eq(0)').length

	#** retorna posição do item
	temp.page.eq = $('#'+$(temp.page.this)[0].id).index('.section-page') if temp.page.this
	temp.item.eq = $('#'+$(temp.item.this)[0].id).index('#'+$(temp.page.this)[0].id+' .section-page-item') if temp.item.this and temp.page.this


	#== Localiza as seções ativas
	# Valida display na pagina
	if !temp.page.this is false

		# caso a pagina atual tenha display
		temp.page.display = true if temp.page.this.hasClass(display)

		# caso a pagina atual nao tenha display 
		if temp.page.display is false

			# Localiza de cima para baixo a pagina ativa
			temp.it.page.this = $(temp.pattern).find('.section-page.'+display) if $(temp.pattern).find('.section-page.'+display).length > 0
			temp.it.page.eq = $('#'+$(temp.it.page.this)[0].id).index('.section-page') if temp.it.page.this

	# Valida display do item
	if !temp.item.this is false

		# caso a pagina atual tenha display
		temp.item.display = true if temp.item.this.hasClass(display)

		# caso a pagina atual nao tenha display 
		if temp.item.display is false

			# Localiza de cima para baixo a pagina ativa
			temp.it.item.this = $(temp.pattern).find('.section-page-item.'+display) if $(temp.pattern).find('.section-page-item.'+display).length > 0
			temp.it.item.eq = $('#'+$(temp.it.item.this)[0].id).index('#'+$(temp.it.page.this)[0].id+' .section-page-item') if temp.it.item.this and temp.it.page.this

	#== Valida o ponto de referencia se é after, before, this, first
	# valida a direção da pagina
	temp.position.page = 'this' if temp.page.display is true # display esta em this
	temp.position.page = 'first' if temp.page.display is false and temp.it.page.this is false # nao foi encontrado
	temp.position.page = 'before' if temp.page.eq < temp.it.page.eq # page=0 it=1 // page esta antes
	temp.position.page = 'after' if temp.page.eq > temp.it.page.eq # page=3 it=1 // page esta depois

	# valida a direção do item
	temp.position.item = 'this' if temp.item.display is true # display esta em this
	temp.position.item = 'first' if temp.item.display is false and temp.it.item.this is false # nao foi encontrado
	temp.position.item = 'before' if temp.item.eq < temp.it.item.eq # item=0 it=1 // item esta antes
	temp.position.item = 'after' if temp.item.eq > temp.it.item.eq # item=3 it=1 // item esta depois

	return temp

$.appCtrl.display = (post) ->
	# {"display":{"._.required":false,"._.list":["put","who","toogle"],"._.type":["array"],"put":{"._.//":"Define o seletor a receber os parametros app-display, pode receber um #ID ou .class","._.required":true,"._.type":["string"]},"who":{"._.//":"Define onde sera encontrado o seletor de @{put} para aplicar os parametros","._.required":false,"._.type":["boolean"],"._.exacly":{"string":["closset","child","this"]}},"toogle":{"._.//":"Define um grupo para ser removido os parametros app-display, deve receber um #ID ou .class","._.required":false,"._.type":["array"],"._.exacly":{"array":"string"}},"no":{"._.//":"Define o parametro app-no-display no seletor de @{put}","._.required":false,"._.type":["bolean"],"._.exacly":{"bolean":"true"}},"inverse":{"._.//":"Define uma ação de ativar/desativar nos parametros de app-display, no mesmo acionador @_{this}","._.required":false,"._.type":["bolean"],"._.exacly":{"bolean":"true"}}}}
	temp = {'_proccess':{'classe_base':null, '_true':false}, '_erro':{'_true':false}, '_warning':{'_true':false}, '_done':{'_true':false}}

	# console.log post
	$(post.this).click ->

		# define classe base
		temp.classe_base = 'app-display';

		# caso o display seja removido
		if post.app.display.no
			# muda classe base
			temp.classe_base = 'app-no-display';

		# caso o item deva ser o unico a ser exibido, valida se existe uma lista a ser oculta
		if post.app.display.toogle
			i = 0
			while i < post.app.display.toogle.length
				# remove a classe 
				$(post.app.display.toogle[i]).removeClass('app-no-display')
				$(post.app.display.toogle[i]).removeClass('app-display')
				i++

		# valida onde sera aplicado o display
		switch post.app.display.who

			when 'this'
				$(post.this).addClass(temp.classe_base)
				temp._done = true

			when 'closest'
				$($(post.this).closest(post.app.display.put)).addClass(temp.classe_base)
				temp._done = true

			when 'child'
				$($(post.this).find(post.app.display.put)).addClass(temp.classe_base)
				temp._done = true

			when 'all'
				$(post.app.display.put).addClass(temp.classe_base)
				temp._done = true

		# configrua inversão altomática da ação do botão
		if post.app.display.inverse
			
			# caso o display não esteja imprimindo
			if !post.app.display.no
				post.app.display.no = true

			# caso o display esteja imprimindo
			else if post.app.display.no
				post.app.display.no = false

	return temp

$.appCtrl.apr = (post) ->
	# {"apr":{"._.required":false,"._.list":["method"],"._.type":["array"],"method":{"._.//":"Parametro para toogle de video e texto da apresentão","._.required":true,"._.type":["string"],"._.exacly":["video","texto"]}}}
	temp = {'_proccess':{'_true':false}, '_erro':{'_true':false}, '_warning':{'_true':false}, '_done':{'_true':false}}

	# console.log post
	$(post.this).click ->
		$($(this).closest('.app-apr-item')).toggleClass('app-apr-display-video')

		if $($(this).closest('.app-apr-item')).find('iframe').attr('data-video-src') != undefined
			$($(this).closest('.app-apr-item')).find('iframe').attr('src', $($(this).closest('.app-apr-item')).find('iframe').data().videoSrc)
			$($(this).closest('.app-apr-item')).find('iframe').removeAttr('data-video-src')

		temp._done = true

	return temp

$.appCtrl.atividade = (post) ->
	# {"atividade":{"._.required":true,"._.list":["change","true","avaliar"],"._.type":["array"],"change":{"._.//":"Valida se a altarnativa é valida","._.required":true,"._.type":["boolean"]},"true":{"._.//":"Valida se a alternativa é valida","._.required":true,"._.type":["boolean"]},"avaliar":{"._.//":"Ativa botão de avaliação mais próximo","._.required":false,"._.type":["boolean"],"._.exacly":{"boolean":"true"}}}}
	# post.app.atividade
	# post.this
	temp = {'_proccess':{'_true':false}, '_erro':{'_true':false}, '_warning':{'_true':false}, '_done':{'_true':false}, 'btn':{}}

	temp._proccess._true = true
	# adiciona configruação caso a atividade esteja ativa
	if post.app.atividade.change is true
		$(post.this).addClass('true') if post.app.atividade.true is true
		$(post.this).addClass('false') if post.app.atividade.true is false
		temp._proccess.change = true
	
	# ao clicar
	$(post.this).click ->

		# caso a resposta seja automatica
		if !post.app.atividade.avaliar

			if post.app.atividade.change is false

				# caso a atividade seja verdadeira
				if post.app.atividade.true is true
					post.app.atividade.change = true
					$(this).addClass('true')
					$(this).data("appCtrl", post.app)
					# TODO: Salva dados do cliente

				# caso a atividade seja  falsa
				if post.app.atividade.true is false
					post.app.atividade.change = true
					$(this).addClass('false') 
					$(this).data("appCtrl", post.app)
					# TODO: Salva dados do cliente

		# caso a resposta seja por confirmação
		if post.app.atividade.avaliar

			if post.app.atividade.change is false

				$(post.this).toggleClass('on')

				$(this).closest('.app-ati-item').find('.app-ati-item-change').click ->

					$(post.this).closest('.app-ati-item').find('.app-ati-alternativa-item').addClass('off')
					$(post.this).closest('.app-ati-item').find('.app-ati-alternativa-item.on').removeClass('off')

					# caso a atividade seja verdadeira
					if post.app.atividade.true is true
						post.app.atividade.change = true
						$(post.this).addClass('true')
						$(post.this).removeClass('off')
						$(post.this).data("appCtrl", post.app)
						# TODO: Salva dados do cliente

					# caso a atividade seja  falsa
					if post.app.atividade.true is false
						post.app.atividade.change = true
						$(post.this).addClass('false') 
						$(post.this).removeClass('off')
						$(post.this).data("appCtrl", post.app)
						# TODO: Salva dados do cliente


	return temp

$.appCtrl.incorporar = (post) ->
	# console.log post
	if post.app.incorporar is 'src'
		$(post.this).after("<objec data-object class=\"#{$(post.this)[0].className}\"></object>")
		$('[data-object]').load($(post.this)[0].currentSrc)
		$(post.this).remove()


$.appCtrl $("[data-app-ctrl]")

$.appScroll = false

$('.app-cap-section').bind "scroll", ->
	section =  $(this)

	itens = section.find('.section-page-item')

	if $.appScroll is false
		i = 0
		while i < itens.length

			u = 0
			while u < section.find('iframe').length
				if $(section.find('iframe')[u]).offset().top < (section.height() + 10) and $(section.find('iframe')[u]).offset().top > 0
					if $(section.find('iframe')[u]).attr('data-video-src') != undefined
						$(section.find('iframe')[u]).attr('src', $(section.find('iframe')[u]).data().videoSrc)
						$(section.find('iframe')[u]).removeAttr('data-video-src')
				u++
			u = undefined
			# console.log $(itens[i]).offset().top != 0 and (section.height() + ($(itens[i]).offset().top * -1)) if $(itens[i]).offset().top != 0
			# console.log $(itens[i]).height() if $(itens[i]).offset().top != 0
			if $(itens[i]).offset().top != 0 and (section.height() + ($(itens[i]).offset().top * -1)) > $(itens[i]).height()

				proximo = $($(itens[i]).next())
				proximo.addClass('app-no-display')

				if (section.height() + ($(itens[i]).offset().top * -1)) > ($(itens[i]).height() + (section.height() - (section.height() / 2)))

					$.appScroll = true
					$(itens[i]).removeClass('app-display').queue (next) ->
						$.appScroll = false
	
						$('.app-nav-section-item.app-secao').text($(proximo).data().appCtrl.navigation.section)
						$('.app-nav-section-item.app-marker').text('•')
						$('.app-nav-section-item.app-titulo').text($(proximo).data().appCtrl.navigation.page)

						$.appCtrl.sectionDisplay([
							[$(proximo), 'in', 'before', {'this':section, 'position':10}]
						])

						next()
			i++
	i = undefined

# # Inicia tratamentos dos controles  # #
# # # # # # # # # # # # # # # # # # # # #
###
console.log "
 * LivroDigital\n
* FTE Developer - VG Consultoria\n
* LivroDigital Beta V.0.1.1\n
"
###

