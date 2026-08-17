<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tolstenko_block_defaults_schema() {
	$schema = array(
		'main_hero' => array(
			'title'           => '',
			'text'            => '',
			'items'           => array(),
			'btn_text'        => 'Оставить заявку',
			'show_promo'      => true,
			'promo_text'      => '',
			'present_image'   => 0,
			'person_name'     => '',
			'person_position' => '',
			'image'           => 0,
		),
		'guide_banner' => array(
			'enabled'  => false,
			'text'     => '',
			'btn_text' => 'Получить гайд',
			'btn_url'  => '',
		),
		'timed_modal' => array(
			'enabled'       => true,
			'delay_seconds' => 40,
			'title'         => 'Не уходите без ответов!',
			'text'          => 'Получите консультацию по привлечению клиентов — оставьте контакты, и мы перезвоним.',
			'phone'         => '',
		),
		'video_bubble' => array(
			'enabled'       => false,
			'source'        => 'file',
			'video'         => 0,
			'iframe_url'    => '',
			'btn_text'      => 'Консультация',
			'btn_url'       => '',
			'position'      => 'left',
			'delay_seconds' => 5,
			'memory_hours'  => 24,
		),
		'reviews' => array(
			'title'      => 'Отзывы',
			'text'       => '',
			'show_items' => true,
			'ids'        => array(),
			'cards'      => array(),
		),
		'certificates' => array(
			'title' => 'Сертификаты и лицензии',
			'text'  => 'Документы, подтверждающие качество материалов и работ.',
			'items' => array(),
		),
		'actions' => array(
			'title' => 'Акции, бонусы, подарки',
			'items' => array(),
		),
		'actions_section' => array(
			'title' => 'Акции',
			'text'  => '',
		),
		'city' => array(
			'title' => 'Города',
			'text'  => '',
		),
		'vacancies_banner' => array(
			'title' => 'Вакансии',
			'text'  => 'Присоединяйтесь к команде',
			'image' => 0,
		),
		'vacancies_section' => array(
			'title' => 'Открытые вакансии',
			'text'  => '',
		),
		'case_section' => array(
			'title'          => 'Кейсы',
			'text'           => '',
			'posts_per_page' => 4,
			'ids'            => array(),
		),
		'service_section' => array(
			'title'          => 'Услуги',
			'text'           => '',
			'posts_per_page' => 6,
			'ids'            => array(),
		),
		'service_section_filters' => array(
			'title'          => 'Услуги',
			'text'           => '',
			'posts_per_page' => 6,
			'ids'            => array(),
		),
		'service_section_tile' => array(
			'title' => 'Услуги',
			'text'  => '',
		),
		'blog_section' => array(
			'title'          => 'Похожие статьи',
			'text'           => '',
			'posts_per_page' => 12,
			'ids'            => array(),
		),
		'blog_section_filters' => array(
			'title'          => 'Статьи',
			'text'           => '',
			'posts_per_page' => 4,
			'ids'            => array(),
			'btn_text'       => 'Все статьи',
		),
		'blog_section_tile' => array(
			'title'            => 'Статьи',
			'text'             => '',
			'posts_per_page'   => 9,
			'ids'              => array(),
			'sidebar_name'     => '',
			'sidebar_text'     => '',
			'sidebar_btn'      => 'Бесплатный аудит',
			'sidebar_btn_url'  => '',
			'sidebar_photo'    => 0,
		),
		'blog_large_img' => array(
			'image' => 0,
		),
		'blog_video' => array(
			'preview' => 0,
			'url'     => '',
			'iframe'  => '',
		),
		'blog_blockquote' => array(
			'text'         => '',
			'link'         => '',
			'show_author'  => false,
			'image'        => 0,
			'author'       => '',
			'author_under' => '',
			'btn_text'     => '',
			'btn_url'      => '',
		),
		'blog_number_list' => array(
			'items' => array(),
		),
		'blog_warning' => array(
			'items' => array(),
		),
		'blog_seo' => array(
			'title'   => 'Нужна помощь с продвижением?',
			'btn'     => 'Получить консультацию',
			'btn_url' => '',
		),
		'consultation_whatsapp' => array(
			'title'       => 'Напишите нам в WhatsApp',
			'text'        => 'Ответим на вопросы и поможем с расчётом стоимости.',
			'btn_text'    => 'Написать в WhatsApp',
			'btn_url'     => '',
			'color'       => '#25D366',
			'color_hover' => '#1EBE57',
		),
		'consultation_tg' => array(
			'title'    => 'Консультация в Telegram',
			'text'     => 'Быстрые ответы и удобное общение в мессенджере.',
			'btn_text' => 'Написать в Telegram',
			'btn_url'  => '',
			'text_btn' => 'Обычно отвечаем в течение 15 минут',
			'image'    => 0,
		),
		'consultation_tel' => array(
			'title'             => 'Позвоните нам — расскажем всё по телефону',
			'message'           => 'Здравствуйте! Готовы ответить на ваши вопросы и помочь с выбором.',
			'position'          => 'Менеджер по работе с клиентами',
			'btn_tel_text'      => 'Позвонить',
			'btn_messenger_text'=> 'Написать в Telegram',
			'btn_messenger_url' => '',
			'color'             => '#25D366',
			'color_hover'       => '#1EBE57',
			'image'             => 0,
			'phone'             => '',
		),
		'consultation_free' => array(
			'title'          => 'Бесплатная консультация',
			'text'           => 'Оставьте заявку — перезвоним и ответим на вопросы.',
			'subtitle'       => 'Заполните форму',
			'contacts_label' => 'Или свяжитесь с нами:',
			'image'          => 0,
			'phone'          => '',
			'telegram_url'   => '',
			'whatsapp_url'   => '',
			'vk_url'         => '',
		),
		'free_audit' => array(
			'items' => array(
				'Бесплатный аудит текущей рекламы',
				'Рекомендации по улучшению',
				'Предварительный расчёт бюджета',
			),
			'btn_text' => 'Получить аудит',
			'btn_url'  => '',
		),
		'contacts_page' => array(
			'title'     => '',
			'items'     => array(),
			'addresses' => array(),
		),
		'contacts_details' => array(
			'title'      => '',
			'items'      => array(),
			'form_title' => 'Свяжитесь с нами',
			'form_text'  => 'Оставьте заявку и мы свяжемся с вами',
		),
		'contacts_maps' => array(
			'title' => '',
			'items' => array(),
		),
		'different_experiences' => array(
			'title'      => 'Разный опыт — один результат',
			'text'       => 'Подбираем формат работы под ваши задачи и бюджет.',
			'items'      => array(
				'Индивидуальный подход',
				'Прозрачные этапы',
				'Фиксированные сроки',
				'Поддержка после запуска',
			),
			'tg_text'    => 'Написать в Telegram',
			'tg_url'     => '',
			'modal_text' => 'Оставить заявку',
			'modal_url'  => '',
		),
		'partners' => array(
			'title' => 'Наши партнёры',
			'text'  => 'Компании, с которыми мы успешно сотрудничаем.',
			'items' => array(),
		),
		'strategy' => array(
			'title'          => 'Стратегия роста',
			'subtitle'       => 'Что входит в работу',
			'text'           => 'Разрабатываем план продвижения и помогаем внедрить его на практике.',
			'items'          => array(
				'Анализ рынка и конкурентов',
				'Позиционирование и оффер',
				'План каналов и бюджета',
			),
			'btn_text'       => 'Получить стратегию',
			'btn_url'        => '',
			'file_text'      => 'Скачать шаблон',
			'file_url'       => '',
			'contacts_label' => 'Свяжитесь с нами прямо сейчас!',
			'phone'          => '',
			'telegram_url'   => '',
			'telegram_text'  => 'Написать в TG',
			'image'          => 0,
			'image_mob'      => 0,
		),
		'team_cards' => array(
			'title' => 'Наша команда',
			'text'  => 'Специалисты, которые ведут ваши проекты.',
			'items' => array(),
		),
		'tg_channel' => array(
			'title'    => 'Наш Telegram-канал',
			'text'     => 'Кейсы, новости и полезные материалы.',
			'items'    => array(
				'Разборы рекламных кампаний',
				'Чек-листы и шаблоны',
				'Анонсы и спецпредложения',
			),
			'btn_text' => 'Подписаться',
			'btn_url'  => '',
			'image'    => 0,
		),
		'solution' => array(
			'title' => 'Подберём <span>Решение</span>',
			'text'  => '',
			'items' => array(
				'Аудит текущей рекламы и точек роста',
				'Стратегия продвижения под ваши цели',
				'Прозрачный план работ и сроки',
			),
			'items_second' => array(
				'Еженедельные отчёты и корректировки',
				'Команда под ваш проект',
				'Окупаемые гипотезы, а не «для галочки»',
			),
		),
		'one_team' => array(
			'title'    => 'Одна команда — полный цикл работ',
			'btn_text' => 'Обсудить проект',
			'btn_url'  => '',
			'items'    => array(
				array( 'value' => '10+', 'text' => 'лет на рынке' ),
				array( 'value' => '50+', 'text' => 'специалистов в команде' ),
				array( 'value' => '300+', 'text' => 'реализованных проектов' ),
				array( 'value' => '24/7', 'text' => 'поддержка клиентов' ),
			),
		),
		'author' => array(
			'name'            => '',
			'photo'           => 0,
			'btn_text'        => '',
			'btn_url'         => '',
			'list'            => array(),
			'items'           => array(),
			'links_label'     => 'Делюсь экспертизой',
			'links'           => array(),
			'show_bottom'     => true,
			'subtitle'        => '',
			'text'            => '',
			'sublist'         => array(),
			'btn_more_text'   => '',
			'btn_more_url'    => '',
			'award'           => '',
			'award_image'     => 0,
			'right_image'     => 0,
			'speeches'        => array(),
			'btn_invite_text' => '',
			'btn_invite_url'  => '',
		),
		'three_steps' => array(
			'title' => 'Три простых шага',
			'text'  => 'От заявки до результата — понятный процесс.',
			'items' => array(
				'Оставляете заявку и кратко описываете задачу',
				'Мы готовим предложение и согласовываем план',
				'Запускаем работу и держим вас в курсе',
			),
		),
		'doubts' => array(
			'subtitle' => 'Развеиваем сомнения',
			'title'    => 'Возражения перед стартом',
			'items'    => array(
				array(
					'badge' => 'Цена',
					'title' => '«150к в месяц — дорого»',
					'text'  => 'Вы получаете команду и систему, которая приводит заявки годами, а не разовую акцию. На аудите считаем окупаемость — если экономики нет, скажем честно.',
				),
				array(
					'badge' => 'Сроки',
					'title' => '«Клиенты нужны сейчас»',
					'text'  => 'SEO хорошо работает в связке с рекламой: реклама даёт заявки сразу, SEO — удешевляет их со временем. Что можно ускорить — скажем прямо.',
				),
				array(
					'badge' => 'Гарантии',
					'title' => '«Нет гарантий результата»',
					'text'  => 'KPI и финансовая ответственность — в договоре. Не достигли — дорабатываем за свой счёт.',
				),
				array(
					'badge' => 'Опыт',
					'title' => '«Пробовали — только отчёты»',
					'text'  => 'Проблема обычно в подходе: SEO делали в отрыве от заявок. Мы начинаем с аудита и находим, где именно всё встало.',
				),
				array(
					'badge' => 'Ниша',
					'title' => '«Вы не разберётесь»',
					'text'  => 'Перед стартом погружаемся в продукт, ЦА и цикл сделки. Работа с B2B и производством — наш профиль.',
				),
				array(
					'badge' => 'Риск',
					'title' => '«Сайт может просесть»',
					'text'  => 'Только белые методы и аккуратная работа. Правки согласуем, критичные страницы не трогаем без анализа.',
				),
				array(
					'badge' => 'Реклама',
					'title' => '«Проще увеличить рекламу»',
					'text'  => 'Реклама останавливается вместе с оплатой. SEO снижает зависимость от бюджета и удешевляет привлечение вдолгую.',
				),
				array(
					'badge' => 'Время',
					'title' => '«Некогда этим заниматься»',
					'text'  => 'Передаёте канал нам. От вас — только согласования, всё остальное берём на себя.',
				),
			),
		),
		'familiar' => array(
			'subtitle' => 'Знакомая ситуация?',
			'title'    => 'Почему сайт не приносит <span>достаточно</span> клиентов',
			'text'     => 'Если узнали себя хотя бы в двух пунктах — SEO решит это системно.',
			'items'    => array(
				array(
					'title' => 'Сайт есть — заявок нет',
					'text'  => 'Ресурс работает как визитка, а не как канал продаж.',
				),
				array(
					'title' => 'Только реклама и рекомендации',
					'text'  => 'Выключаете рекламу — поток обращений сразу падает.',
				),
				array(
					'title' => 'Реклама дорожает',
					'text'  => 'Каждый месяц бюджет растёт, а заявок столько же.',
				),
				array(
					'title' => 'Пробовали SEO — были отчёты',
					'text'  => 'Позиции росли, а продажи и обращения — нет.',
				),
				array(
					'title' => 'Конкуренты в топе, вас не видно',
					'text'  => 'Клиенты находят их раньше и уходят к ним.',
				),
				array(
					'title' => 'Нет времени разбираться',
					'text'  => 'Нужен подрядчик, которому можно передать канал.',
				),
			),
		),
		'result' => array(
			'subtitle' => 'Отвечаем за результат',
			'title'    => 'Наши гарантии <span>в договоре</span>',
			'items'    => array(
				array(
					'ico'   => 0,
					'title' => 'Гарантия результата',
					'text'  => 'Фиксируем KPI. Не достигли в срок — работаем до результата.',
				),
				array(
					'ico'   => 0,
					'title' => 'Гарантия прозрачности',
					'text'  => 'Видите план/факт и отчёт по заявкам каждый период.',
				),
				array(
					'ico'   => 0,
					'title' => 'Гарантия стоимости',
					'text'  => 'Цена зафиксирована в договоре, без скрытых доплат.',
				),
				array(
					'ico'   => 0,
					'title' => 'Гарантия безопасности',
					'text'  => 'Только белые методы, без риска фильтров.',
				),
			),
		),
		'faq' => array(
			'title'      => 'Частые вопросы',
			'text'       => 'Ответы на популярные вопросы о работе с нами.',
			'items'      => array(
				array(
					'title'    => 'Сколько занимает изготовление?',
					'redactor' => '<p>Сроки зависят от объёма и сложности задачи. Обычно обсуждаем сроки на консультации.</p>',
				),
				array(
					'title'    => 'Можно ли заказать только дизайн?',
					'redactor' => '<p>Да, можем выполнить отдельные этапы: дизайн, производство или монтаж.</p>',
				),
			),
			'form_title' => 'Не нашли ответ на свой вопрос?',
			'form_text'  => 'Оставьте заявку, и мы свяжемся с вами в ближайшее время, чтобы обсудить детали вашего проекта',
			'foto'       => 0,
			'foto_text'  => '',
			'phone'      => '',
			'telegram_url' => '',
		),
		'seo_section' => array(
			'title'     => '',
			'subtitle'  => '',
			'more_text' => 'Читать далее',
			'blocks'    => array(),
		),
		'we_can' => array(
			'title'      => 'Мы можем',
			'items'      => array(
				'Привести клиентов по вашей услуге',
				'Настроить партнёрский канал и отчётность',
				'Сопровождать заявки до передачи вам',
			),
			'list_title' => 'Условия выплат',
			'list'       => array(
				'Выплата после оплаты клиента',
				'Прозрачная фиксация заявок',
				'Регулярные отчёты по партнёрам',
			),
			'form_title' => 'Не нашли ответ на свой вопрос?',
			'form_text'  => 'Оставьте заявку, и мы свяжемся с вами в ближайшее время, чтобы обсудить детали вашего проекта',
		),
		'recomendation' => array(
			'title'      => 'Рекомендации',
			'text'       => 'Выберите удобный формат сотрудничества — мы подстроим процесс под вас.',
			'items'      => array(
				array(
					'ico'   => 0,
					'title' => 'Для агентств',
					'text'  => 'Передавайте задачи на производство и монтаж — мы берём операционку на себя.',
				),
				array(
					'ico'   => 0,
					'title' => 'Для интеграторов',
					'text'  => 'Подключайте нас как подрядчика по изготовлению и установке.',
				),
			),
			'list_title' => 'При любом варианте',
			'list'       => array(
				'Прозрачные условия и сроки',
				'Персональный менеджер',
				'Отчётность по заявкам',
			),
			'btn_text'   => 'Стать партнёром',
			'btn_url'    => '',
		),
		'referal' => array(
			'title'      => 'Рефералка',
			'items'      => array(
				'Рекомендуете нас клиенту',
				'Клиент оплачивает услугу',
				'Вы получаете вознаграждение',
			),
			'list_title' => 'Условия выплат',
			'list'       => array(
				'Выплата после оплаты клиента',
				'Прозрачная фиксация заявок',
				'Регулярные отчёты по партнёрам',
			),
			'btn_text'   => 'Оставить заявку',
			'btn_url'    => '',
		),
		'commission' => array(
			'title' => 'Вознаграждение',
			'text'  => 'Прозрачные условия — вы заранее понимаете размер выплаты.',
			'items' => array(
				array(
					'ico'        => 0,
					'title'      => 'Разовая услуга',
					'summa'      => 'от 100 000 ₽',
					'time'       => 'Разовая',
					'commission' => '10%',
					'remark'     => 'от суммы заказа',
				),
				array(
					'ico'        => 0,
					'title'      => 'Долгосрочный проект',
					'summa'      => 'от 300 000 ₽',
					'time'       => 'от 3 месяцев',
					'commission' => '15%',
					'remark'     => 'от суммы заказа',
				),
			),
		),
		'benefits_cooperation' => array(
			'title' => 'Преимущества сотрудничества',
			'items' => array(
				array(
					'list' => array(
						array(
							'title' => 'Быстрый старт',
							'text'  => 'Подключаем партнёра и даём материалы для рекомендаций.',
						),
						array(
							'title' => 'Прозрачный учёт',
							'text'  => 'Каждая заявка фиксируется и доступна в отчётах.',
						),
					),
					'btn_text' => 'Стать партнёром',
					'btn_url'  => '',
				),
				array(
					'list' => array(
						array(
							'title' => 'Поддержка менеджера',
							'text'  => 'Отвечаем на вопросы и помогаем закрывать сделки.',
						),
						array(
							'title' => 'Стабильные выплаты',
							'text'  => 'Вознаграждение после оплаты клиента.',
						),
					),
					'btn_text' => 'Задать вопрос',
					'btn_url'  => '',
				),
			),
		),
		'aducation' => array(
			'title'  => 'Образование',
			'items'  => array(
				array(
					'year'       => '2010',
					'type'       => 'Высшее',
					'title'      => 'Университет',
					'speciality' => 'Специальность',
				),
			),
			'images' => array(),
		),
		'clients' => array(
			'title'        => 'Клиенты',
			'text'         => '',
			'items'        => array(),
			'show_top'     => true,
			'subtitle'     => 'СМИ',
			'smi'          => array(),
			'show_bottom'  => true,
		),
		'themes' => array(
			'title'     => 'Темы обучений и выступлений',
			'items'     => array(
				'Маркетинг и продажи',
				'Управление командой',
				'Личный бренд',
			),
			'more_text' => 'и многое другое',
			'btn_text'  => 'Пригласить',
			'btn_url'   => '',
			'image'     => 0,
		),
		'collaboration' => array(
			'title'    => 'Форматы сотрудничества',
			'items'    => array(
				'Лекции и мастер-классы',
				'Корпоративное обучение',
				'Менторство',
			),
			'btn_text' => 'Обсудить формат',
			'btn_url'  => '',
			'image'    => 0,
		),
	);

	if ( function_exists( 'tolstenko_vacancy_template_schema' ) ) {
		$schema = array_merge( $schema, tolstenko_vacancy_template_schema() );
	}

	return $schema;
}

/**
 * Нормализация ID записей для слайдеров.
 */
function tolstenko_sanitize_ids( $raw ) {
	$ids = array();
	if ( is_string( $raw ) ) {
		$raw = preg_split( '/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY );
	}
	if ( ! is_array( $raw ) ) {
		return array();
	}
	foreach ( $raw as $id ) {
		$id = (int) $id;
		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}
	return array_values( array_unique( $ids ) );
}

/**
 * Получение списка записей для select2-подобного поля.
 */
function tolstenko_get_posts_for_select( $post_type ) {
	$posts = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);
	$result = array();
	foreach ( $posts as $post ) {
		$result[] = array(
			'id'    => $post->ID,
			'title' => get_the_title( $post ),
		);
	}
	return $result;
}

/**
 * Рендер поля мультивыбора записей (как в Gutenberg).
 *
 * @param string               $name         Имя input (без []).
 * @param int[]|mixed          $selected_ids Выбранные ID.
 * @param string               $post_type    CPT.
 * @param string               $label        Подпись над полем.
 * @param array<string,mixed>  $args         exclude_ids, placeholder.
 */
function tolstenko_render_post_select( $name, $selected_ids, $post_type, $label = '', $args = array() ) {
	$args         = is_array( $args ) ? $args : array();
	$exclude_ids  = array_map( 'intval', (array) ( $args['exclude_ids'] ?? array() ) );
	$exclude_ids  = array_values( array_filter( $exclude_ids ) );
	$all_posts    = tolstenko_get_posts_for_select( $post_type );
	if ( $exclude_ids ) {
		$all_posts = array_values(
			array_filter(
				$all_posts,
				static function ( $p ) use ( $exclude_ids ) {
					return ! in_array( (int) ( $p['id'] ?? 0 ), $exclude_ids, true );
				}
			)
		);
	}
	$selected_ids = array_map( 'intval', (array) $selected_ids );
	$selected_ids = array_values( array_filter( $selected_ids, static function ( $id ) use ( $exclude_ids ) {
		return $id > 0 && ! in_array( $id, $exclude_ids, true );
	} ) );

	$placeholders = array(
		'service' => 'Поиск услуг...',
		'blog'    => 'Поиск статей...',
		'case'    => 'Поиск кейсов...',
		'review'  => 'Поиск отзывов...',
		'actions' => 'Поиск акций...',
	);
	$placeholder = (string) ( $args['placeholder'] ?? '' );
	if ( $placeholder === '' ) {
		$placeholder = $placeholders[ $post_type ] ?? ( 'Поиск ' . $post_type . '...' );
	}

	$posts_json = wp_json_encode( $all_posts, JSON_UNESCAPED_UNICODE );
	if ( ! is_string( $posts_json ) ) {
		$posts_json = '[]';
	}
	?>
	<div class="tolstenko-post-select-wrap">
		<?php if ( $label ) : ?>
			<div class="muted" style="margin-bottom:6px;"><?php echo esc_html( $label ); ?></div>
		<?php endif; ?>
		<div
			class="tolstenko-post-select-token-field"
			data-name="<?php echo esc_attr( $name ); ?>"
			data-post-type="<?php echo esc_attr( $post_type ); ?>"
			data-posts="<?php echo esc_attr( $posts_json ); ?>"
		>
			<div class="tolstenko-post-select-tokens">
				<?php foreach ( $selected_ids as $id ) : ?>
					<?php
					$title = '';
					foreach ( $all_posts as $p ) {
						if ( (int) $p['id'] === $id ) {
							$title = (string) $p['title'];
							break;
						}
					}
					if ( ! $title ) {
						$title = get_the_title( $id );
						$title = $title !== '' ? $title : ( '#' . $id );
					}
					?>
					<span class="tolstenko-post-select-token" data-id="<?php echo (int) $id; ?>">
						<span class="tolstenko-post-select-token-label"><?php echo esc_html( $title ); ?></span>
						<button type="button" class="tolstenko-post-select-token-remove" title="Удалить">×</button>
						<input type="hidden" name="<?php echo esc_attr( $name ); ?>[]" value="<?php echo (int) $id; ?>">
					</span>
				<?php endforeach; ?>
			</div>
			<div class="tolstenko-post-select-input-wrap">
				<input type="text" class="tolstenko-post-select-input" placeholder="<?php echo esc_attr( $placeholder ); ?>" autocomplete="off">
				<div class="tolstenko-post-select-suggestions" style="display:none;"></div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * CSS + JS для tolstenko_render_post_select (один раз за запрос).
 */
function tolstenko_post_select_print_assets() {
	static $printed = false;
	if ( $printed ) {
		return;
	}
	$printed = true;
	?>
	<style>
		.tolstenko-post-select-wrap{border:1px solid #dcdcde;border-radius:4px;padding:8px;background:#fff;width:100%;box-sizing:border-box}
		.tolstenko-post-select-tokens{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px}
		.tolstenko-post-select-tokens:empty{display:none;margin:0}
		.tolstenko-post-select-token{display:inline-flex;align-items:center;background:#e5e5e5;border-radius:3px;padding:4px 8px;gap:6px;font-size:13px;max-width:100%}
		.tolstenko-post-select-token-label{overflow:hidden;text-overflow:ellipsis}
		.tolstenko-post-select-token-remove{background:none;border:none;color:#b32d2e;cursor:pointer;font-size:18px;line-height:1;padding:0 2px;flex-shrink:0}
		.tolstenko-post-select-token-remove:hover{color:#7b1a1b}
		.tolstenko-post-select-input-wrap{position:relative}
		.tolstenko-post-select-input{width:100%;max-width:none !important;padding:6px 8px;border:1px solid #ddd;border-radius:3px;box-sizing:border-box}
		.tolstenko-post-select-suggestions{position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #ddd;border-radius:3px;max-height:240px;overflow:auto;z-index:100000;box-shadow:0 2px 6px rgba(0,0,0,0.15)}
		.tolstenko-post-select-suggestions .suggestion{padding:6px 10px;cursor:pointer}
		.tolstenko-post-select-suggestions .suggestion:hover{background:#f0f0f0}
	</style>
	<script>
	(function(){
		function stripHtml(s){
			var d = document.createElement('div');
			d.innerHTML = String(s || '');
			return (d.textContent || d.innerText || '').trim();
		}
		function initPostSelect() {
			document.querySelectorAll('.tolstenko-post-select-token-field').forEach(function(wrap) {
				if (wrap.dataset.initialized) return;
				wrap.dataset.initialized = '1';
				var input = wrap.querySelector('.tolstenko-post-select-input');
				var suggestions = wrap.querySelector('.tolstenko-post-select-suggestions');
				var tokensContainer = wrap.querySelector('.tolstenko-post-select-tokens');
				var name = wrap.dataset.name;
				var postType = wrap.dataset.postType || '';
				if (!input || !suggestions || !tokensContainer || !name) return;

				var allPosts = [];
				var isLoading = false;
				var isLoaded = false;

				try {
					if (wrap.hasAttribute('data-posts')) {
						var embedded = JSON.parse(wrap.getAttribute('data-posts') || '[]');
						if (Array.isArray(embedded)) {
							allPosts = embedded.map(function(p){
								return { id: parseInt(p.id, 10), title: stripHtml(p.title) || ('#' + p.id) };
							}).filter(function(p){ return p.id > 0; });
							suggestions.dataset.posts = JSON.stringify(allPosts);
							isLoaded = true;
						}
					}
				} catch (e) {}

				function loadAllPosts(cb) {
					if (isLoaded) { if (cb) cb(); return; }
					if (isLoading) return;
					isLoading = true;

					var existingIds = {};
					tokensContainer.querySelectorAll('.tolstenko-post-select-token').forEach(function(token) {
						var id = parseInt(token.dataset.id, 10);
						existingIds[id] = true;
						allPosts.push({
							id: id,
							title: stripHtml(token.querySelector('.tolstenko-post-select-token-label').textContent)
						});
					});

					if (!postType) {
						isLoaded = true;
						isLoading = false;
						suggestions.dataset.posts = JSON.stringify(allPosts);
						if (cb) cb();
						return;
					}

					var restRoot = (window.wpApiSettings && wpApiSettings.root) ? wpApiSettings.root : '/wp-json/';
					fetch(restRoot + 'wp/v2/' + postType + '?per_page=100&status=publish&_fields=id,title')
						.then(function(res) { return res.json(); })
						.then(function(data) {
							if (!Array.isArray(data)) data = [];
							data.forEach(function(post) {
								if (!existingIds[post.id]) {
									allPosts.push({ id: post.id, title: stripHtml(post.title && post.title.rendered) || ('#' + post.id) });
								}
							});
							allPosts.sort(function(a, b) { return a.title.localeCompare(b.title, 'ru'); });
							suggestions.dataset.posts = JSON.stringify(allPosts);
							isLoaded = true;
							isLoading = false;
							if (cb) cb();
						})
						.catch(function() {
							isLoaded = true;
							isLoading = false;
							suggestions.dataset.posts = JSON.stringify(allPosts);
							if (cb) cb();
						});
				}

				function getSelectedIds() {
					var ids = [];
					tokensContainer.querySelectorAll('.tolstenko-post-select-token').forEach(function(token) {
						ids.push(parseInt(token.dataset.id, 10));
					});
					return ids;
				}

				function showSuggestions(filter) {
					var posts = [];
					try { posts = JSON.parse(suggestions.dataset.posts || '[]'); } catch (e) { posts = []; }
					var selectedIds = getSelectedIds();
					var query = (filter || '').toLowerCase().trim();
					var filtered = posts.filter(function(p) {
						if (selectedIds.indexOf(p.id) !== -1) return false;
						if (query) return String(p.title || '').toLowerCase().indexOf(query) !== -1;
						return true;
					});

					if (filtered.length === 0) {
						suggestions.style.display = 'none';
						return;
					}

					suggestions.innerHTML = '';
					filtered.forEach(function(post) {
						var div = document.createElement('div');
						div.className = 'suggestion';
						div.textContent = post.title;
						div.dataset.id = post.id;
						div.addEventListener('mousedown', function(e) {
							e.preventDefault();
							addToken(post.id, post.title);
							input.value = '';
							showSuggestions('');
						});
						suggestions.appendChild(div);
					});
					suggestions.style.display = 'block';
				}

				function addToken(id, title) {
					var existing = tokensContainer.querySelector('.tolstenko-post-select-token[data-id="' + id + '"]');
					if (existing) return;
					var token = document.createElement('span');
					token.className = 'tolstenko-post-select-token';
					token.dataset.id = String(id);
					token.innerHTML = '<span class="tolstenko-post-select-token-label"></span>' +
						'<button type="button" class="tolstenko-post-select-token-remove" title="Удалить">×</button>' +
						'<input type="hidden" name="' + name + '[]" value="' + id + '">';
					token.querySelector('.tolstenko-post-select-token-label').textContent = title;
					tokensContainer.appendChild(token);
					token.querySelector('.tolstenko-post-select-token-remove').addEventListener('click', function() {
						token.remove();
						if (document.activeElement === input) showSuggestions(input.value);
					});
				}

				input.addEventListener('focus', function() {
					loadAllPosts(function(){ showSuggestions(input.value); });
				});
				input.addEventListener('input', function() {
					loadAllPosts(function(){ showSuggestions(input.value); });
				});
				input.addEventListener('blur', function() {
					setTimeout(function(){ suggestions.style.display = 'none'; }, 180);
				});
				document.addEventListener('click', function(e) {
					if (!wrap.contains(e.target)) suggestions.style.display = 'none';
				});
				tokensContainer.querySelectorAll('.tolstenko-post-select-token-remove').forEach(function(btn) {
					btn.addEventListener('click', function() {
						btn.closest('.tolstenko-post-select-token').remove();
						if (document.activeElement === input) showSuggestions(input.value);
					});
				});
			});
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', initPostSelect);
		} else {
			initPostSelect();
		}
		if (window.MutationObserver) {
			new MutationObserver(function(){ initPostSelect(); }).observe(document.body, { childList: true, subtree: true });
		}
		window.tolstenkoInitPostSelect = initPostSelect;
	})();
	</script>
	<?php
}

/**
 * Нормализация ID услуг в дефолтах слайдера.
 *
 * @param mixed $raw Сырые ID (массив или строка).
 * @return int[]
 */
function tolstenko_sanitize_service_section_ids( $raw ) {
	return tolstenko_sanitize_ids( $raw );
}

/**
 * Поля дефолтов для слайдера услуг (обычный / с фильтрами).
 */
function tolstenko_render_service_section_defaults_fields( $key, $data ) {
	$key  = sanitize_key( $key );
	$data = is_array( $data ) ? $data : array();
	$ids  = tolstenko_sanitize_service_section_ids( $data['ids'] ?? array() );
	?>
	<div class="row"><textarea name="tolstenko_block_defaults[<?php echo esc_attr( $key ); ?>][title]" rows="2" style="width:100%" placeholder="Заголовок (HTML)"><?php echo esc_textarea( $data['title'] ?? '' ); ?></textarea></div>
	<div class="row"><textarea name="tolstenko_block_defaults[<?php echo esc_attr( $key ); ?>][text]" rows="3" style="width:100%" placeholder="Текст под заголовком"><?php echo esc_textarea( $data['text'] ?? '' ); ?></textarea></div>
	<div class="row"><input type="number" min="-1" name="tolstenko_block_defaults[<?php echo esc_attr( $key ); ?>][posts_per_page]" value="<?php echo esc_attr( (string) ( $data['posts_per_page'] ?? 6 ) ); ?>" style="width:100%" placeholder="Количество услуг, если ничего не выбрано (6, -1 = все)"></div>
	<div class="row">
		<?php tolstenko_render_post_select( 
			'tolstenko_block_defaults[' . $key . '][ids]',
			$ids,
			'service',
			'Услуги (пусто = самые новые по количеству выше)'
		); ?>
	</div>
	<?php
}

/**
 * Поля дефолтов для слайдера статей.
 */
function tolstenko_render_blog_section_defaults_fields( $key, $data ) {
	$key  = sanitize_key( $key );
	$data = is_array( $data ) ? $data : array();
	$ids  = tolstenko_sanitize_service_section_ids( $data['ids'] ?? array() );
	?>
	<div class="row"><textarea name="tolstenko_block_defaults[<?php echo esc_attr( $key ); ?>][title]" rows="2" style="width:100%" placeholder="Заголовок (HTML)"><?php echo esc_textarea( $data['title'] ?? '' ); ?></textarea></div>
	<div class="row"><textarea name="tolstenko_block_defaults[<?php echo esc_attr( $key ); ?>][text]" rows="3" style="width:100%" placeholder="Текст под заголовком"><?php echo esc_textarea( $data['text'] ?? '' ); ?></textarea></div>
	<div class="row"><input type="number" min="-1" name="tolstenko_block_defaults[<?php echo esc_attr( $key ); ?>][posts_per_page]" value="<?php echo esc_attr( (string) ( $data['posts_per_page'] ?? 6 ) ); ?>" style="width:100%" placeholder="Количество статей, если ничего не выбрано (6, -1 = все)"></div>
	<div class="row">
		<?php tolstenko_render_post_select( 
			'tolstenko_block_defaults[' . $key . '][ids]',
			$ids,
			'blog',
			'Статьи (пусто = самые новые по количеству выше)'
		); ?>
	</div>
	<?php
}

/**
 * Список ли это (0..n), а не ассоциативный массив полей.
 */
function tolstenko_is_list_array( $value ) {
	if ( ! is_array( $value ) ) {
		return false;
	}
	if ( array() === $value ) {
		return true;
	}
	return array_keys( $value ) === range( 0, count( $value ) - 1 );
}

/**
 * Склейка схемы и сохранённых дефолтов.
 */
function tolstenko_merge_block_defaults_data( $base, $saved ) {
	if ( ! is_array( $base ) ) {
		$base = array();
	}
	if ( ! is_array( $saved ) ) {
		return $base;
	}
	$data = array_replace_recursive( $base, $saved );
	foreach ( $saved as $key => $value ) {
		if ( ! is_array( $value ) ) {
			continue;
		}
		$base_val = $base[ $key ] ?? null;
		if ( tolstenko_is_list_array( $value ) && ( null === $base_val || tolstenko_is_list_array( $base_val ) ) ) {
			$data[ $key ] = $value;
			continue;
		}
		if ( is_array( $base_val ) && ! tolstenko_is_list_array( $base_val ) && ! tolstenko_is_list_array( $value ) ) {
			$data[ $key ] = tolstenko_merge_block_defaults_data( $base_val, $value );
		}
	}
	return $data;
}

function tolstenko_get_block_defaults( $block ) {
	$schema = tolstenko_block_defaults_schema();
	$base = isset( $schema[ $block ] ) ? $schema[ $block ] : array();
	$saved = get_option( 'tolstenko_block_defaults', array() );
	if ( ! is_array( $saved ) || ! isset( $saved[ $block ] ) || ! is_array( $saved[ $block ] ) ) {
		if ( $block === 'service_section_filters' && is_array( $saved ) && isset( $saved['service_section'] ) && is_array( $saved['service_section'] ) ) {
			$data = tolstenko_merge_block_defaults_data( $base, $saved['service_section'] );
			$data['ids'] = tolstenko_sanitize_service_section_ids( $data['ids'] ?? array() );
			return $data;
		}
		return $base;
	}
	$data = tolstenko_merge_block_defaults_data( $base, $saved[ $block ] );
	if ( $block === 'service_section' || $block === 'service_section_filters' || $block === 'blog_section' || $block === 'blog_section_filters' || $block === 'blog_section_tile' || $block === 'case_section' || $block === 'reviews' ) {
		$data['ids'] = tolstenko_sanitize_service_section_ids( $data['ids'] ?? array() );
	}
	return $data;
}

add_action( 'admin_menu', 'tolstenko_register_block_defaults_admin_page' );
add_action( 'admin_enqueue_scripts', 'tolstenko_block_defaults_admin_assets' );

function tolstenko_register_block_defaults_admin_page() {
	add_menu_page(
		__( 'Настройки сайта', 'tolstenko-theme' ),
		__( 'Настройки сайта', 'tolstenko-theme' ),
		'manage_options',
		'tolstenko-site-settings',
		'tolstenko_render_block_defaults_admin_page',
		'dashicons-admin-generic',
		58
	);
	add_submenu_page(
		'tolstenko-site-settings',
		__( 'Дефолты блоков', 'tolstenko-theme' ),
		__( 'Дефолты блоков', 'tolstenko-theme' ),
		'manage_options',
		'tolstenko-site-settings',
		'tolstenko_render_block_defaults_admin_page'
	);
}

function tolstenko_block_defaults_admin_assets( $hook ) {
	if ( $hook !== 'toplevel_page_tolstenko-site-settings' ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_editor();
	wp_enqueue_script( 'jquery' );
	wp_enqueue_style( 'wp-components' );
}

/**
 * Визуальный редактор ответа FAQ в дефолтах блоков.
 */
function tolstenko_faq_answer_editor( $content, $idx ) {
	$idx       = (int) $idx;
	$editor_id = 'tolstenko_faq_redactor_' . $idx;
	wp_editor(
		(string) $content,
		$editor_id,
		array(
			'textarea_name' => 'tolstenko_block_defaults[faq][items][' . $idx . '][redactor]',
			'textarea_rows' => 8,
			'media_buttons' => true,
			'teeny'         => false,
			'quicktags'     => true,
			'editor_height' => 180,
		)
	);
}

function tolstenko_render_block_defaults_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['tolstenko_block_defaults_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_block_defaults_nonce'] ) ), 'tolstenko_block_defaults_save' ) ) {
		tolstenko_save_block_defaults_from_request();
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Дефолты блоков сохранены.', 'tolstenko-theme' ) . '</p></div>';
	}

	$all = tolstenko_block_defaults_schema();
	$saved = get_option( 'tolstenko_block_defaults', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	foreach ( $saved as $block_key => $block_saved ) {
		if ( ! is_array( $block_saved ) ) {
			continue;
		}
		$base_block = isset( $all[ $block_key ] ) && is_array( $all[ $block_key ] ) ? $all[ $block_key ] : array();
		$all[ $block_key ] = tolstenko_merge_block_defaults_data( $base_block, $block_saved );
	}
	if ( empty( $all['certificates']['items'] ) || ! is_array( $all['certificates']['items'] ) ) {
		$all['certificates']['items'] = array();
	}
	?>
	<div class="wrap">
	<h1><?php esc_html_e( 'Настройки сайта: дефолты блоков', 'tolstenko-theme' ); ?></h1>
	<form method="post" action="">
	<?php wp_nonce_field( 'tolstenko_block_defaults_save', 'tolstenko_block_defaults_nonce' ); ?>
	<p class="description"><?php esc_html_e( 'Редактируйте дефолтные значения блоков. Они используются, когда у конкретной страницы/блока нет своих значений.', 'tolstenko-theme' ); ?></p>
	<style>
	.tolstenko-df-tabs{display:flex;flex-wrap:wrap;gap:0;border-bottom:1px solid #dcdcde;margin-top:8px}
	.tolstenko-df-tabs-group{margin-top:20px;padding:14px;background:#fff;border:1px solid #dcdcde;border-radius:4px}
	.tolstenko-df-tabs-group.is-active{border-color:#2271b1;box-shadow:0 0 0 1px #2271b1}
	.tolstenko-df-tabs-group-title{margin:0;font-size:14px;font-weight:600;color:#1d2327}
	.tolstenko-df-tab{border:1px solid #dcdcde;border-bottom:0;background:#f0f0f1;padding:8px 12px;cursor:pointer;margin:0 4px 0 0;border-radius:4px 4px 0 0}
	.tolstenko-df-tab.active{background:#fff;font-weight:600;color:#1d2327;position:relative;z-index:1}
	.tolstenko-df-group-panels{border:1px solid #dcdcde;border-top:0;background:#fff;min-height:0}
	.tolstenko-df-group-panels:empty{display:none}
	.tolstenko-df-panel{display:none;padding:14px}
	.tolstenko-df-panel.active{display:block}
	.tolstenko-df .row{margin:10px 0}
	.tolstenko-df textarea{width:100%}
	.tolstenko-df .repeater-item{padding:10px;border:1px solid #ddd;background:#fafafa;margin-bottom:8px}
	.tolstenko-df .repeater-item .cols{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
	.tolstenko-df .repeater-item .cols input[type="text"]{flex:1 1 220px}
	.tolstenko-df .repeater-item .cols .move-btn{min-width:34px;padding:0 8px}
	.tolstenko-df .muted{font-size:12px;color:#666}
	.tolstenko-df .actions{margin-top:12px;display:flex;gap:8px}
	.tolstenko-df .icon-preview img{max-width:44px;max-height:44px;display:block}
	.tolstenko-df .cert-preview img{max-width:80px;max-height:110px;display:block;object-fit:cover;border-radius:4px}
	.tolstenko-df .tolstenko-defaults-image-row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
	.tolstenko-df .tolstenko-faq-editor-wrap .wp-editor-wrap{width:100%}
	.tolstenko-df .tolstenko-faq-editor-wrap .wp-editor-area{width:100%}
	.tolstenko-df .repeater-item .repeater-item{margin-top:8px;background:#f5f5f5}
	</style>
	<?php tolstenko_post_select_print_assets(); ?>
	<div class="tolstenko-df">
		<div class="tolstenko-df-tabs-group is-active" data-group="main">
			<div class="tolstenko-df-tabs-group-title"><?php esc_html_e( 'Основные блоки', 'tolstenko-theme' ); ?></div>
			<div class="tolstenko-df-tabs">
				<button type="button" class="tolstenko-df-tab active" data-panel="main_hero" data-group="main"><?php esc_html_e( 'Главный баннер', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="free_audit" data-group="main"><?php esc_html_e( 'Бесплатный аудит', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="solution" data-group="main"><?php esc_html_e( 'Решение', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="consultation_tel" data-group="main"><?php esc_html_e( 'Консультация tel', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="one_team" data-group="main"><?php esc_html_e( 'Одна команда', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="actions" data-group="main"><?php esc_html_e( 'Акции, бонусы', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="consultation_tg" data-group="main"><?php esc_html_e( 'Консультация TG', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="different_experiences" data-group="main"><?php esc_html_e( 'Разный опыт', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="three_steps" data-group="main"><?php esc_html_e( 'Три шага', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="doubts" data-group="main"><?php esc_html_e( 'Сомнения', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="familiar" data-group="main"><?php esc_html_e( 'Знакомая ситуация', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="result" data-group="main"><?php esc_html_e( 'Результат', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="strategy" data-group="main"><?php esc_html_e( 'Стратегия', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="author" data-group="main"><?php esc_html_e( 'Автор', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="team_cards" data-group="main"><?php esc_html_e( 'Команда', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="partners" data-group="main"><?php esc_html_e( 'Партнёры', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="certificates" data-group="main"><?php esc_html_e( 'Сертификаты', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="guide_banner" data-group="main"><?php esc_html_e( 'Гайд-баннер', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="timed_modal" data-group="main"><?php esc_html_e( 'Модалка по таймеру', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="city" data-group="main"><?php esc_html_e( 'Города', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="consultation_whatsapp" data-group="main"><?php esc_html_e( 'Забронируйте место', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="consultation_free" data-group="main"><?php esc_html_e( 'Бесплатная консультация', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="tg_channel" data-group="main"><?php esc_html_e( 'TG-канал', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="faq" data-group="main"><?php esc_html_e( 'FAQ', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="seo_section" data-group="main"><?php esc_html_e( 'SEO продвижение', 'tolstenko-theme' ); ?></button>
			</div>
			<div class="tolstenko-df-group-panels" data-group-panels="main"></div>
		</div>
		<div class="tolstenko-df-tabs-group" data-group="post_sliders">
			<div class="tolstenko-df-tabs-group-title"><?php esc_html_e( 'Слайдера записей', 'tolstenko-theme' ); ?></div>
			<div class="tolstenko-df-tabs">
				<button type="button" class="tolstenko-df-tab" data-panel="case_section" data-group="post_sliders"><?php esc_html_e( 'Кейсы', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="reviews" data-group="post_sliders"><?php esc_html_e( 'Отзывы', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="service_section" data-group="post_sliders"><?php esc_html_e( 'Слайдер услуг', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="service_section_filters" data-group="post_sliders"><?php esc_html_e( 'Слайдер услуг (фильтры)', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="service_section_tile" data-group="post_sliders"><?php esc_html_e( 'Услуги (плитка)', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="blog_section" data-group="post_sliders"><?php esc_html_e( 'Слайдер статей', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="blog_section_filters" data-group="post_sliders"><?php esc_html_e( 'Статьи', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="blog_section_tile" data-group="post_sliders"><?php esc_html_e( 'Статьи плитка', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="actions_section" data-group="post_sliders"><?php esc_html_e( 'Плитка акций', 'tolstenko-theme' ); ?></button>
			</div>
			<div class="tolstenko-df-group-panels" data-group-panels="post_sliders"></div>
		</div>
		<div class="tolstenko-df-tabs-group" data-group="vacancies">
			<div class="tolstenko-df-tabs-group-title"><?php esc_html_e( 'Вакансии — блоки', 'tolstenko-theme' ); ?></div>
			<div class="tolstenko-df-tabs">
				<button type="button" class="tolstenko-df-tab" data-panel="vacancies_banner" data-group="vacancies"><?php esc_html_e( 'Баннер вакансий', 'tolstenko-theme' ); ?></button>
				<button type="button" class="tolstenko-df-tab" data-panel="vacancies_section" data-group="vacancies"><?php esc_html_e( 'Секция вакансий', 'tolstenko-theme' ); ?></button>
			</div>
			<div class="tolstenko-df-group-panels" data-group-panels="vacancies"></div>
		</div>

		<div class="tolstenko-df-panels-source">
		<div class="tolstenko-df-panel active" data-panel="main_hero" data-group="main">
			<?php
			$mh         = $all['main_hero'] ?? array();
			$mh_img     = isset( $mh['image'] ) ? (int) $mh['image'] : 0;
			$mh_img_url = $mh_img ? wp_get_attachment_image_url( $mh_img, 'medium' ) : '';
			$mh_present = isset( $mh['present_image'] ) ? (int) $mh['present_image'] : 0;
			$mh_present_url = $mh_present ? wp_get_attachment_image_url( $mh_present, 'thumbnail' ) : '';
			?>
			<div class="row"><textarea name="tolstenko_block_defaults[main_hero][title]" rows="2" style="width:100%" placeholder="Заголовок (HTML)"><?php echo esc_textarea( $mh['title'] ?? '' ); ?></textarea></div>
			<div class="row"><textarea name="tolstenko_block_defaults[main_hero][text]" rows="3" style="width:100%" placeholder="Текст под заголовком (HTML)"><?php echo esc_textarea( $mh['text'] ?? '' ); ?></textarea></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[main_hero][btn_text]" value="<?php echo esc_attr( $mh['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки"></div>
			<div class="row">
				<label>
					<input type="checkbox" name="tolstenko_block_defaults[main_hero][show_promo]" value="1" <?php checked( ! empty( $mh['show_promo'] ) ); ?>>
					<?php esc_html_e( 'Показать промо у кнопки', 'tolstenko-theme' ); ?>
				</label>
			</div>
			<div class="row"><textarea name="tolstenko_block_defaults[main_hero][promo_text]" rows="2" style="width:100%" placeholder="Текст промо у кнопки (HTML)"><?php echo esc_textarea( $mh['promo_text'] ?? '' ); ?></textarea></div>
			<div class="row tolstenko-defaults-image-row">
				<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[main_hero][present_image]" value="<?php echo (int) $mh_present; ?>">
				<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Иконка подарка', 'tolstenko-theme' ); ?></button>
				<span class="icon-preview"><?php echo $mh_present_url ? '<img src="' . esc_url( $mh_present_url ) . '" style="max-height:40px">' : ''; ?></span>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[main_hero][person_name]" value="<?php echo esc_attr( $mh['person_name'] ?? '' ); ?>" style="width:100%" placeholder="Имя персоны"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[main_hero][person_position]" value="<?php echo esc_attr( $mh['person_position'] ?? '' ); ?>" style="width:100%" placeholder="Должность"></div>
			<div class="row tolstenko-defaults-image-row">
				<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[main_hero][image]" value="<?php echo (int) $mh_img; ?>">
				<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Главное изображение', 'tolstenko-theme' ); ?></button>
				<span class="icon-preview"><?php echo $mh_img_url ? '<img src="' . esc_url( $mh_img_url ) . '" style="max-height:80px">' : ''; ?></span>
			</div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Пункты списка (HTML)', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="main-hero-list">
					<?php foreach ( (array) ( $mh['items'] ?? array() ) as $idx => $txt ) : ?>
						<?php $item_val = is_string( $txt ) ? $txt : (string) ( $txt['text'] ?? '' ); ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<textarea name="tolstenko_block_defaults[main_hero][items][<?php echo (int) $idx; ?>]" rows="2" style="width:100%"><?php echo esc_textarea( $item_val ); ?></textarea>
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<button type="button" class="button" data-add-item="main-hero-list"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="guide_banner" data-group="main">
			<?php $gb = $all['guide_banner'] ?? array(); ?>
			<div class="row">
				<label>
					<input type="checkbox" name="tolstenko_block_defaults[guide_banner][enabled]" value="1" <?php checked( ! empty( $gb['enabled'] ) ); ?>>
					<?php esc_html_e( 'Показывать гайд-баннер', 'tolstenko-theme' ); ?>
				</label>
			</div>
			<p class="description"><?php esc_html_e( 'Появляется под шапкой через 10 секунд (только desktop). После закрытия скрывается на 24 часа.', 'tolstenko-theme' ); ?></p>
			<div class="row"><input type="text" name="tolstenko_block_defaults[guide_banner][text]" value="<?php echo esc_attr( $gb['text'] ?? '' ); ?>" style="width:100%" placeholder="Текст уведомления"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[guide_banner][btn_text]" value="<?php echo esc_attr( $gb['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[guide_banner][btn_url]" value="<?php echo esc_attr( $gb['btn_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка кнопки (пусто → модалка заявки)"></div>
		</div>

		<div class="tolstenko-df-panel" data-panel="timed_modal" data-group="main">
			<?php $tm = $all['timed_modal'] ?? array(); ?>
			<div class="row">
				<label>
					<input type="checkbox" name="tolstenko_block_defaults[timed_modal][enabled]" value="1" <?php checked( ! empty( $tm['enabled'] ) ); ?>>
					<?php esc_html_e( 'Показывать модалку заявки по таймеру', 'tolstenko-theme' ); ?>
				</label>
			</div>
			<p class="description"><?php esc_html_e( 'Отдельная плашка (#modal-timed): логотип, телефон, заголовок + та же CF7. Память закрытия — как у гайд-баннера (24 часа). Обычная #modal не затрагивается.', 'tolstenko-theme' ); ?></p>
			<div class="row">
				<label for="tolstenko_timed_modal_delay"><strong><?php esc_html_e( 'Задержка, секунд', 'tolstenko-theme' ); ?></strong></label><br>
				<input type="number" id="tolstenko_timed_modal_delay" name="tolstenko_block_defaults[timed_modal][delay_seconds]" value="<?php echo esc_attr( (string) (int) ( $tm['delay_seconds'] ?? 40 ) ); ?>" min="5" max="600" step="1" style="width:120px">
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[timed_modal][title]" value="<?php echo esc_attr( $tm['title'] ?? '' ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Заголовок', 'tolstenko-theme' ); ?>"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[timed_modal][text]" rows="3" style="width:100%" placeholder="<?php esc_attr_e( 'Текст под заголовком', 'tolstenko-theme' ); ?>"><?php echo esc_textarea( $tm['text'] ?? '' ); ?></textarea></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[timed_modal][phone]" value="<?php echo esc_attr( $tm['phone'] ?? '' ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Телефон (пусто = из контактных данных сайта)', 'tolstenko-theme' ); ?>"></div>
		</div>

		<div class="tolstenko-df-panel" data-panel="reviews" data-group="post_sliders">
			<?php
			$rv      = $all['reviews'] ?? array();
			$rv_ids  = isset( $rv['ids'] ) && is_array( $rv['ids'] ) ? array_map( 'intval', $rv['ids'] ) : array();
			$rv_cards = isset( $rv['cards'] ) && is_array( $rv['cards'] ) ? $rv['cards'] : array();
			?>
			<div class="row"><textarea name="tolstenko_block_defaults[reviews][title]" rows="2" style="width:100%" placeholder="Заголовок (HTML)"><?php echo esc_textarea( $rv['title'] ?? '' ); ?></textarea></div>
			<div class="row"><textarea name="tolstenko_block_defaults[reviews][text]" rows="3" style="width:100%" placeholder="Текст под заголовком"><?php echo esc_textarea( $rv['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<label>
					<input type="checkbox" name="tolstenko_block_defaults[reviews][show_items]" value="1" <?php checked( ! empty( $rv['show_items'] ) ); ?>>
					<?php esc_html_e( 'Показывать блок рейтингов (reviews__items)', 'tolstenko-theme' ); ?>
				</label>
			</div>
			<div class="row">
				<?php tolstenko_render_post_select( 
					'tolstenko_block_defaults[reviews][ids]',
					$rv_ids,
					'review',
					'Отзывы (пусто = все)'
				); ?>
			</div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Карточки рейтингов (reviews__items)', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="reviews-cards-list">
					<?php foreach ( $rv_cards as $idx => $card ) : ?>
						<?php
						$card    = is_array( $card ) ? $card : array();
						$c_title = (string) ( $card['title'] ?? '' );
						$c_url   = (string) ( $card['url'] ?? '' );
						$c_rating = isset( $card['rating'] ) ? (int) $card['rating'] : 5;
						?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[reviews][cards][<?php echo (int) $idx; ?>][title]" value="<?php echo esc_attr( $c_title ); ?>" placeholder="Название (Яндекс, 2ГИС…)" style="flex:1">
								<input type="url" name="tolstenko_block_defaults[reviews][cards][<?php echo (int) $idx; ?>][url]" value="<?php echo esc_attr( $c_url ); ?>" placeholder="Ссылка" style="flex:1">
								<input type="number" min="1" max="5" name="tolstenko_block_defaults[reviews][cards][<?php echo (int) $idx; ?>][rating]" value="<?php echo (int) $c_rating; ?>" style="width:80px" title="Рейтинг">
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<button type="button" class="button" data-add-item="reviews-cards-list"><?php esc_html_e( 'Добавить карточку', 'tolstenko-theme' ); ?></button>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="certificates" data-group="main">
			<div class="row"><input type="text" name="tolstenko_block_defaults[certificates][title]" value="<?php echo esc_attr( $all['certificates']['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[certificates][text]" rows="3" placeholder="Текст под заголовком"><?php echo esc_textarea( $all['certificates']['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Сертификаты (изображение + подпись)', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="certificates-list">
					<?php foreach ( (array) ( $all['certificates']['items'] ?? array() ) as $idx => $it ) : ?>
						<?php
						$it        = is_array( $it ) ? $it : array();
						$img_id    = isset( $it['image'] ) ? (int) $it['image'] : 0;
						$img_url   = $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '';
						$img_title = isset( $it['title'] ) ? (string) $it['title'] : '';
						?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[certificates][items][<?php echo (int) $idx; ?>][title]" value="<?php echo esc_attr( $img_title ); ?>" placeholder="Подпись / alt">
								<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[certificates][items][<?php echo (int) $idx; ?>][image]" value="<?php echo (int) $img_id; ?>">
								<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать изображение', 'tolstenko-theme' ); ?></button>
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="icon-preview cert-preview" style="margin-top:8px;">
								<?php if ( $img_url ) : ?>
									<img src="<?php echo esc_url( $img_url ); ?>" alt="">
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions">
					<button type="button" class="button" data-add-item="certificates-list"><?php esc_html_e( 'Добавить сертификат', 'tolstenko-theme' ); ?></button>
				</div>
			</div>
		</div>

		<?php
		$act_slider = $all['actions'] ?? array();
		$action_posts = get_posts(
			array(
				'post_type'      => 'actions',
				'posts_per_page' => 100,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		?>
		<div class="tolstenko-df-panel" data-panel="actions">
			<div class="row"><input type="text" name="tolstenko_block_defaults[actions][title]" value="<?php echo esc_attr( $act_slider['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок секции"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Карточки (до 4). Текст на карточке свой; запись «Акции» — только ссылка.', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="actions-list">
					<?php foreach ( (array) ( $act_slider['items'] ?? array() ) as $idx => $it ) : ?>
						<?php
						$it  = is_array( $it ) ? $it : array();
						$aid = isset( $it['action_id'] ) ? (int) $it['action_id'] : 0;
						?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[actions][items][<?php echo (int) $idx; ?>][type]" value="<?php echo esc_attr( $it['type'] ?? '' ); ?>" placeholder="Тип / метка">
								<input type="text" name="tolstenko_block_defaults[actions][items][<?php echo (int) $idx; ?>][title]" value="<?php echo esc_attr( $it['title'] ?? '' ); ?>" placeholder="Заголовок карточки">
							</div>
							<div class="row"><textarea name="tolstenko_block_defaults[actions][items][<?php echo (int) $idx; ?>][text]" rows="2" placeholder="Текст карточки"><?php echo esc_textarea( $it['text'] ?? '' ); ?></textarea></div>
							<div class="cols">
								<select name="tolstenko_block_defaults[actions][items][<?php echo (int) $idx; ?>][action_id]" style="min-width:220px">
									<option value="0"><?php esc_html_e( '— Без ссылки —', 'tolstenko-theme' ); ?></option>
									<?php foreach ( $action_posts as $ap ) : ?>
										<option value="<?php echo (int) $ap->ID; ?>" <?php selected( $aid, (int) $ap->ID ); ?>><?php echo esc_html( get_the_title( $ap ) ); ?></option>
									<?php endforeach; ?>
								</select>
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="actions-list"><?php esc_html_e( 'Добавить карточку', 'tolstenko-theme' ); ?></button></div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="actions_section" data-group="post_sliders">
			<div class="row"><input type="text" name="tolstenko_block_defaults[actions_section][title]" value="<?php echo esc_attr( $all['actions_section']['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[actions_section][text]" rows="3" placeholder="Текст под заголовком"><?php echo esc_textarea( $all['actions_section']['text'] ?? '' ); ?></textarea></div>
			<p class="muted"><?php esc_html_e( 'Карточки берутся из записей «Акции» (миниатюра + мета-поля).', 'tolstenko-theme' ); ?></p>
		</div>

		<div class="tolstenko-df-panel" data-panel="city">
			<div class="row"><input type="text" name="tolstenko_block_defaults[city][title]" value="<?php echo esc_attr( $all['city']['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[city][text]" rows="3" placeholder="Текст под заголовком"><?php echo esc_textarea( $all['city']['text'] ?? '' ); ?></textarea></div>
			<p class="muted"><?php esc_html_e( 'Список берётся из записей «Город».', 'tolstenko-theme' ); ?></p>
		</div>

		<div class="tolstenko-df-panel" data-panel="vacancies_banner">
			<?php
			$vb       = $all['vacancies_banner'] ?? array();
			$vb_img   = isset( $vb['image'] ) ? (int) $vb['image'] : 0;
			$vb_url   = $vb_img ? wp_get_attachment_image_url( $vb_img, 'medium' ) : '';
			?>
			<div class="row"><input type="text" name="tolstenko_block_defaults[vacancies_banner][title]" value="<?php echo esc_attr( $vb['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[vacancies_banner][text]" rows="3" placeholder="Текст"><?php echo esc_textarea( $vb['text'] ?? '' ); ?></textarea></div>
			<div class="row tolstenko-defaults-image-row">
				<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[vacancies_banner][image]" value="<?php echo (int) $vb_img; ?>">
				<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать изображение', 'tolstenko-theme' ); ?></button>
				<div class="icon-preview" style="margin-top:8px;">
					<?php if ( $vb_url ) : ?>
						<img src="<?php echo esc_url( $vb_url ); ?>" alt="">
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="vacancies_section">
			<div class="row"><input type="text" name="tolstenko_block_defaults[vacancies_section][title]" value="<?php echo esc_attr( $all['vacancies_section']['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[vacancies_section][text]" rows="3" placeholder="Текст под заголовком"><?php echo esc_textarea( $all['vacancies_section']['text'] ?? '' ); ?></textarea></div>
			<p class="muted"><?php esc_html_e( 'Карточки и фильтр берутся из CPT «Вакансии» и таксономии vacancy_cat.', 'tolstenko-theme' ); ?></p>
		</div>

		<?php
		$cw = $all['consultation_whatsapp'] ?? array();
		$ctg = $all['consultation_tg'] ?? array();
		$ctel = $all['consultation_tel'] ?? array();
		$cfree = $all['consultation_free'] ?? array();
		$ctg_img_id = isset( $ctg['image'] ) ? (int) $ctg['image'] : 0;
		$ctg_img_url = $ctg_img_id ? wp_get_attachment_image_url( $ctg_img_id, 'medium' ) : '';
		$ctel_img_id = isset( $ctel['image'] ) ? (int) $ctel['image'] : 0;
		$ctel_img_url = $ctel_img_id ? wp_get_attachment_image_url( $ctel_img_id, 'medium' ) : '';
		$cfree_img_id = isset( $cfree['image'] ) ? (int) $cfree['image'] : 0;
		$cfree_img_url = $cfree_img_id ? wp_get_attachment_image_url( $cfree_img_id, 'medium' ) : '';
		?>

		<div class="tolstenko-df-panel" data-panel="consultation_whatsapp">
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_whatsapp][title]" value="<?php echo esc_attr( $cw['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[consultation_whatsapp][text]" rows="3" placeholder="Текст"><?php echo esc_textarea( $cw['text'] ?? '' ); ?></textarea></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_whatsapp][btn_text]" value="<?php echo esc_attr( $cw['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[consultation_whatsapp][btn_url]" value="<?php echo esc_attr( $cw['btn_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка кнопки (https://wa.me/...)"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_whatsapp][color]" value="<?php echo esc_attr( $cw['color'] ?? '#25D366' ); ?>" style="width:48%" placeholder="Цвет кнопки"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_whatsapp][color_hover]" value="<?php echo esc_attr( $cw['color_hover'] ?? '#1EBE57' ); ?>" style="width:48%" placeholder="Цвет hover"></div>
		</div>

		<div class="tolstenko-df-panel" data-panel="consultation_tg" data-group="main">
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_tg][title]" value="<?php echo esc_attr( $ctg['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[consultation_tg][text]" rows="3" placeholder="Текст"><?php echo esc_textarea( $ctg['text'] ?? '' ); ?></textarea></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_tg][btn_text]" value="<?php echo esc_attr( $ctg['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[consultation_tg][btn_url]" value="<?php echo esc_attr( $ctg['btn_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка Telegram"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_tg][text_btn]" value="<?php echo esc_attr( $ctg['text_btn'] ?? '' ); ?>" style="width:100%" placeholder="Подпись под кнопкой"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Фото / аватар', 'tolstenko-theme' ); ?></div>
				<div class="tolstenko-defaults-image-row">
					<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[consultation_tg][image]" value="<?php echo (int) $ctg_img_id; ?>">
					<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать изображение', 'tolstenko-theme' ); ?></button>
					<div class="icon-preview"><?php if ( $ctg_img_url ) : ?><img src="<?php echo esc_url( $ctg_img_url ); ?>" alt=""><?php endif; ?></div>
				</div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="consultation_tel" data-group="main">
			<div class="row"><textarea name="tolstenko_block_defaults[consultation_tel][title]" rows="2" placeholder="Заголовок"><?php echo esc_textarea( $ctel['title'] ?? '' ); ?></textarea></div>
			<div class="row"><textarea name="tolstenko_block_defaults[consultation_tel][message]" rows="3" placeholder="Текст в пузыре"><?php echo esc_textarea( $ctel['message'] ?? '' ); ?></textarea></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_tel][position]" value="<?php echo esc_attr( $ctel['position'] ?? '' ); ?>" style="width:100%" placeholder="Должность / подпись"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_tel][phone]" value="<?php echo esc_attr( $ctel['phone'] ?? '' ); ?>" style="width:100%" placeholder="Телефон (если пусто — из шапки/подвала)"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_tel][btn_tel_text]" value="<?php echo esc_attr( $ctel['btn_tel_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки звонка"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_tel][btn_messenger_text]" value="<?php echo esc_attr( $ctel['btn_messenger_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки мессенджера"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[consultation_tel][btn_messenger_url]" value="<?php echo esc_attr( $ctel['btn_messenger_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка мессенджера"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_tel][color]" value="<?php echo esc_attr( $ctel['color'] ?? '#25D366' ); ?>" style="width:48%" placeholder="Цвет кнопки мессенджера"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_tel][color_hover]" value="<?php echo esc_attr( $ctel['color_hover'] ?? '#1EBE57' ); ?>" style="width:48%" placeholder="Цвет hover"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Аватар менеджера', 'tolstenko-theme' ); ?></div>
				<div class="tolstenko-defaults-image-row">
					<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[consultation_tel][image]" value="<?php echo (int) $ctel_img_id; ?>">
					<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать изображение', 'tolstenko-theme' ); ?></button>
					<div class="icon-preview"><?php if ( $ctel_img_url ) : ?><img src="<?php echo esc_url( $ctel_img_url ); ?>" alt=""><?php endif; ?></div>
				</div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="consultation_free">
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_free][title]" value="<?php echo esc_attr( $cfree['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[consultation_free][text]" rows="3" placeholder="Текст"><?php echo esc_textarea( $cfree['text'] ?? '' ); ?></textarea></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_free][subtitle]" value="<?php echo esc_attr( $cfree['subtitle'] ?? '' ); ?>" style="width:100%" placeholder="Подзаголовок формы"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_free][contacts_label]" value="<?php echo esc_attr( $cfree['contacts_label'] ?? '' ); ?>" style="width:100%" placeholder="Подпись «Или свяжитесь с нами»"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[consultation_free][phone]" value="<?php echo esc_attr( $cfree['phone'] ?? '' ); ?>" style="width:100%" placeholder="Телефон (если пусто — из шапки/подвала)"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[consultation_free][telegram_url]" value="<?php echo esc_attr( $cfree['telegram_url'] ?? '' ); ?>" style="width:100%" placeholder="Telegram URL"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[consultation_free][whatsapp_url]" value="<?php echo esc_attr( $cfree['whatsapp_url'] ?? '' ); ?>" style="width:100%" placeholder="WhatsApp URL"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[consultation_free][vk_url]" value="<?php echo esc_attr( $cfree['vk_url'] ?? '' ); ?>" style="width:100%" placeholder="VK URL"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Изображение справа', 'tolstenko-theme' ); ?></div>
				<div class="tolstenko-defaults-image-row">
					<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[consultation_free][image]" value="<?php echo (int) $cfree_img_id; ?>">
					<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать изображение', 'tolstenko-theme' ); ?></button>
					<div class="icon-preview"><?php if ( $cfree_img_url ) : ?><img src="<?php echo esc_url( $cfree_img_url ); ?>" alt=""><?php endif; ?></div>
				</div>
			</div>
		</div>

		<?php
		$fa = $all['free_audit'] ?? array();
		$sol = $all['solution'] ?? array();
		$ot  = $all['one_team'] ?? array();
		$au  = $all['author'] ?? array();
		$de = $all['different_experiences'] ?? array();
		$pr = $all['partners'] ?? array();
		if ( empty( $fa['items'] ) || ! is_array( $fa['items'] ) ) {
			$fa['items'] = array();
		}
		if ( empty( $sol['items'] ) || ! is_array( $sol['items'] ) ) {
			$sol['items'] = array();
		}
		if ( empty( $sol['items_second'] ) || ! is_array( $sol['items_second'] ) ) {
			$sol['items_second'] = array();
		}
		if ( empty( $ot['items'] ) || ! is_array( $ot['items'] ) ) {
			$ot['items'] = array();
		}
		if ( empty( $au['list'] ) || ! is_array( $au['list'] ) ) {
			$au['list'] = array();
		}
		if ( empty( $au['items'] ) || ! is_array( $au['items'] ) ) {
			$au['items'] = array();
		}
		if ( empty( $au['links'] ) || ! is_array( $au['links'] ) ) {
			$au['links'] = array();
		}
		if ( empty( $au['sublist'] ) || ! is_array( $au['sublist'] ) ) {
			$au['sublist'] = array();
		}
		if ( empty( $au['speeches'] ) || ! is_array( $au['speeches'] ) ) {
			$au['speeches'] = array();
		}
		$au_photo_id  = isset( $au['photo'] ) ? (int) $au['photo'] : 0;
		$au_photo_url = $au_photo_id ? wp_get_attachment_image_url( $au_photo_id, 'thumbnail' ) : '';
		$au_award_id  = isset( $au['award_image'] ) ? (int) $au['award_image'] : 0;
		$au_award_url = $au_award_id ? wp_get_attachment_image_url( $au_award_id, 'thumbnail' ) : '';
		$au_right_id  = isset( $au['right_image'] ) ? (int) $au['right_image'] : 0;
		$au_right_url = $au_right_id ? wp_get_attachment_image_url( $au_right_id, 'thumbnail' ) : '';
		if ( empty( $de['items'] ) || ! is_array( $de['items'] ) ) {
			$de['items'] = array();
		}
		if ( empty( $pr['items'] ) || ! is_array( $pr['items'] ) ) {
			$pr['items'] = array();
		}
		?>

		<div class="tolstenko-df-panel" data-panel="free_audit" data-group="main">
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Пункты списка', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="free-audit-list">
					<?php foreach ( (array) $fa['items'] as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[free_audit][items][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Текст пункта">
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions">
					<button type="button" class="button" data-add-item="free-audit-list"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button>
				</div>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[free_audit][btn_text]" value="<?php echo esc_attr( $fa['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[free_audit][btn_url]" value="<?php echo esc_attr( $fa['btn_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка кнопки (пусто = модалка заявки)"></div>
		</div>

		<div class="tolstenko-df-panel" data-panel="solution" data-group="main">
			<div class="row"><textarea name="tolstenko_block_defaults[solution][title]" rows="2" style="width:100%" placeholder="Заголовок (HTML)"><?php echo esc_textarea( $sol['title'] ?? '' ); ?></textarea></div>
			<div class="row"><textarea name="tolstenko_block_defaults[solution][text]" rows="3" style="width:100%" placeholder="Текст под заголовком"><?php echo esc_textarea( $sol['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Первый ряд (HTML)', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="solution-list">
					<?php foreach ( (array) $sol['items'] as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<textarea name="tolstenko_block_defaults[solution][items][<?php echo (int) $idx; ?>]" rows="2" style="width:100%" placeholder="Текст пункта (HTML)"><?php echo esc_textarea( is_array( $txt ) ? (string) ( $txt['text'] ?? '' ) : (string) $txt ); ?></textarea>
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions">
					<button type="button" class="button" data-add-item="solution-list"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button>
				</div>
			</div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Второй ряд (HTML, необязательно)', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="solution-list-second">
					<?php foreach ( (array) $sol['items_second'] as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<textarea name="tolstenko_block_defaults[solution][items_second][<?php echo (int) $idx; ?>]" rows="2" style="width:100%" placeholder="Текст пункта (HTML)"><?php echo esc_textarea( is_array( $txt ) ? (string) ( $txt['text'] ?? '' ) : (string) $txt ); ?></textarea>
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions">
					<button type="button" class="button" data-add-item="solution-list-second"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button>
				</div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="one_team" data-group="main">
			<div class="row"><textarea name="tolstenko_block_defaults[one_team][title]" rows="2" style="width:100%" placeholder="Заголовок (HTML)"><?php echo esc_textarea( $ot['title'] ?? '' ); ?></textarea></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[one_team][btn_text]" value="<?php echo esc_attr( $ot['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[one_team][btn_url]" value="<?php echo esc_attr( $ot['btn_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка кнопки (пусто = модалка заявки)"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Показатели', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="one-team-list">
					<?php foreach ( (array) $ot['items'] as $idx => $it ) : ?>
						<?php $it = is_array( $it ) ? $it : array( 'value' => '', 'text' => '' ); ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[one_team][items][<?php echo (int) $idx; ?>][value]" value="<?php echo esc_attr( $it['value'] ?? '' ); ?>" placeholder="Значение (10+)">
								<input type="text" name="tolstenko_block_defaults[one_team][items][<?php echo (int) $idx; ?>][text]" value="<?php echo esc_attr( $it['text'] ?? '' ); ?>" placeholder="Подпись">
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions">
					<button type="button" class="button" data-add-item="one-team-list"><?php esc_html_e( 'Добавить показатель', 'tolstenko-theme' ); ?></button>
				</div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="author" data-group="main">
			<div class="row"><textarea name="tolstenko_block_defaults[author][name]" rows="2" style="width:100%" placeholder="Имя автора (HTML)"><?php echo esc_textarea( $au['name'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Фото', 'tolstenko-theme' ); ?></div>
				<div class="tolstenko-defaults-image-row">
					<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[author][photo]" value="<?php echo (int) $au_photo_id; ?>">
					<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать фото', 'tolstenko-theme' ); ?></button>
					<div class="icon-preview"><?php if ( $au_photo_url ) : ?><img src="<?php echo esc_url( $au_photo_url ); ?>" alt=""><?php endif; ?></div>
				</div>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[author][btn_text]" value="<?php echo esc_attr( $au['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки под фото"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[author][btn_url]" value="<?php echo esc_attr( $au['btn_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка кнопки под фото (пусто = модалка заявки)"></div>
			<p class="description" style="margin-top:-6px;margin-bottom:12px;"><?php esc_html_e( 'Если ссылка пустая — кнопка открывает модалку заявки (#modal).', 'tolstenko-theme' ); ?></p>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Список под именем', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="author-list">
					<?php foreach ( (array) $au['list'] as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[author][list][<?php echo (int) $idx; ?>][text]" value="<?php echo esc_attr( is_array( $txt ) ? (string) ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Пункт списка" style="width:100%">
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="author-list"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></div>
			</div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Показатели (слайдер)', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="author-items">
					<?php foreach ( (array) $au['items'] as $idx => $it ) : ?>
						<?php $it = is_array( $it ) ? $it : array( 'value' => '', 'text' => '' ); ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[author][items][<?php echo (int) $idx; ?>][value]" value="<?php echo esc_attr( $it['value'] ?? '' ); ?>" placeholder="Значение">
								<input type="text" name="tolstenko_block_defaults[author][items][<?php echo (int) $idx; ?>][text]" value="<?php echo esc_attr( $it['text'] ?? '' ); ?>" placeholder="Подпись">
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="author-items"><?php esc_html_e( 'Добавить показатель', 'tolstenko-theme' ); ?></button></div>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[author][links_label]" value="<?php echo esc_attr( $au['links_label'] ?? '' ); ?>" style="width:100%" placeholder="Подпись над ссылками"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Ссылки (иконка + текст + URL)', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="author-links">
					<?php foreach ( (array) $au['links'] as $idx => $it ) : ?>
						<?php
						$it      = is_array( $it ) ? $it : array();
						$icon_id = isset( $it['icon'] ) ? (int) $it['icon'] : 0;
						$icon_url = $icon_id ? wp_get_attachment_image_url( $icon_id, 'thumbnail' ) : '';
						?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[author][links][<?php echo (int) $idx; ?>][title]" value="<?php echo esc_attr( $it['title'] ?? '' ); ?>" placeholder="Текст ссылки">
								<input type="url" name="tolstenko_block_defaults[author][links][<?php echo (int) $idx; ?>][url]" value="<?php echo esc_attr( $it['url'] ?? '' ); ?>" placeholder="URL">
								<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[author][links][<?php echo (int) $idx; ?>][icon]" value="<?php echo (int) $icon_id; ?>">
								<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Иконка', 'tolstenko-theme' ); ?></button>
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="icon-preview" style="margin-top:8px;"><?php if ( $icon_url ) : ?><img src="<?php echo esc_url( $icon_url ); ?>" alt=""><?php endif; ?></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="author-links"><?php esc_html_e( 'Добавить ссылку', 'tolstenko-theme' ); ?></button></div>
			</div>
			<hr>
			<div class="row">
				<label>
					<input type="checkbox" name="tolstenko_block_defaults[author][show_bottom]" value="1" <?php checked( ! empty( $au['show_bottom'] ) ); ?>>
					<?php esc_html_e( 'Показывать нижний блок', 'tolstenko-theme' ); ?>
				</label>
			</div>
			<div class="row"><textarea name="tolstenko_block_defaults[author][subtitle]" rows="2" style="width:100%" placeholder="Подзаголовок нижнего блока (HTML)"><?php echo esc_textarea( $au['subtitle'] ?? '' ); ?></textarea></div>
			<div class="row"><textarea name="tolstenko_block_defaults[author][text]" rows="3" style="width:100%" placeholder="Текст нижнего блока"><?php echo esc_textarea( $au['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Список в нижнем блоке', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="author-sublist">
					<?php foreach ( (array) $au['sublist'] as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[author][sublist][<?php echo (int) $idx; ?>][text]" value="<?php echo esc_attr( is_array( $txt ) ? (string) ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Пункт" style="width:100%">
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="author-sublist"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></div>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[author][btn_more_text]" value="<?php echo esc_attr( $au['btn_more_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки «Подробнее»"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[author][btn_more_url]" value="<?php echo esc_attr( $au['btn_more_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка кнопки «Подробнее»"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[author][award]" rows="2" style="width:100%" placeholder="Текст награды"><?php echo esc_textarea( $au['award'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Изображение награды', 'tolstenko-theme' ); ?></div>
				<div class="tolstenko-defaults-image-row">
					<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[author][award_image]" value="<?php echo (int) $au_award_id; ?>">
					<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать', 'tolstenko-theme' ); ?></button>
					<div class="icon-preview"><?php if ( $au_award_url ) : ?><img src="<?php echo esc_url( $au_award_url ); ?>" alt=""><?php endif; ?></div>
				</div>
			</div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Правое изображение', 'tolstenko-theme' ); ?></div>
				<div class="tolstenko-defaults-image-row">
					<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[author][right_image]" value="<?php echo (int) $au_right_id; ?>">
					<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать', 'tolstenko-theme' ); ?></button>
					<div class="icon-preview"><?php if ( $au_right_url ) : ?><img src="<?php echo esc_url( $au_right_url ); ?>" alt=""><?php endif; ?></div>
				</div>
			</div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Выступления', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="author-speeches">
					<?php foreach ( (array) $au['speeches'] as $idx => $it ) : ?>
						<?php
						$it      = is_array( $it ) ? $it : array();
						$img_id  = isset( $it['image'] ) ? (int) $it['image'] : 0;
						$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
						?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[author][speeches][<?php echo (int) $idx; ?>][text]" value="<?php echo esc_attr( $it['text'] ?? '' ); ?>" placeholder="Подпись" style="flex:1 1 220px">
								<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[author][speeches][<?php echo (int) $idx; ?>][image]" value="<?php echo (int) $img_id; ?>">
								<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Фото', 'tolstenko-theme' ); ?></button>
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="icon-preview" style="margin-top:8px;"><?php if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" alt=""><?php endif; ?></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="author-speeches"><?php esc_html_e( 'Добавить выступление', 'tolstenko-theme' ); ?></button></div>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[author][btn_invite_text]" value="<?php echo esc_attr( $au['btn_invite_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки приглашения"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[author][btn_invite_url]" value="<?php echo esc_attr( $au['btn_invite_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка кнопки приглашения"></div>
		</div>

		<div class="tolstenko-df-panel" data-panel="case_section" data-group="post_sliders">
			<?php
			$cs = $all['case_section'] ?? array();
			$cs_ids = isset( $cs['ids'] ) && is_array( $cs['ids'] ) ? array_map( 'intval', $cs['ids'] ) : array();
			?>
			<div class="row"><textarea name="tolstenko_block_defaults[case_section][title]" rows="2" style="width:100%" placeholder="Заголовок (HTML)"><?php echo esc_textarea( $cs['title'] ?? '' ); ?></textarea></div>
			<div class="row"><textarea name="tolstenko_block_defaults[case_section][text]" rows="3" style="width:100%" placeholder="Текст под заголовком"><?php echo esc_textarea( $cs['text'] ?? '' ); ?></textarea></div>
			<div class="row"><input type="number" min="-1" name="tolstenko_block_defaults[case_section][posts_per_page]" value="<?php echo esc_attr( (string) ( $cs['posts_per_page'] ?? 4 ) ); ?>" style="width:100%" placeholder="Количество кейсов (4, -1 = все)"></div>
			<div class="row">
				<?php tolstenko_render_post_select( 
					'tolstenko_block_defaults[case_section][ids]',
					$cs_ids,
					'case',
					'Кейсы (пусто = все)'
				); ?>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="service_section" data-group="post_sliders">
			<?php tolstenko_render_service_section_defaults_fields( 'service_section', $all['service_section'] ?? array() ); ?>
		</div>

		<div class="tolstenko-df-panel" data-panel="service_section_filters" data-group="post_sliders">
			<?php
			$filters_data = $all['service_section_filters'] ?? array();
			if ( ! array_key_exists( 'service_section_filters', $saved ) && ! empty( $all['service_section'] ) && is_array( $all['service_section'] ) ) {
				$filters_data = array_replace_recursive( $filters_data, $all['service_section'] );
			}
			tolstenko_render_service_section_defaults_fields( 'service_section_filters', $filters_data );
			?>
		</div>

		<div class="tolstenko-df-panel" data-panel="service_section_tile" data-group="post_sliders">
			<div class="row"><textarea name="tolstenko_block_defaults[service_section_tile][title]" rows="2" style="width:100%" placeholder="Заголовок (HTML)"><?php echo esc_textarea( $all['service_section_tile']['title'] ?? '' ); ?></textarea></div>
			<div class="row"><textarea name="tolstenko_block_defaults[service_section_tile][text]" rows="3" style="width:100%" placeholder="Текст под заголовком"><?php echo esc_textarea( $all['service_section_tile']['text'] ?? '' ); ?></textarea></div>
			<p class="description"><?php esc_html_e( 'Плитка выводит все услуги с фильтром по категориям и кнопкой «Показать ещё» (после 6 карточек).', 'tolstenko-theme' ); ?></p>
		</div>

		<div class="tolstenko-df-panel" data-panel="blog_section" data-group="post_sliders">
			<?php tolstenko_render_blog_section_defaults_fields( 'blog_section', $all['blog_section'] ?? array() ); ?>
		</div>

		<div class="tolstenko-df-panel" data-panel="blog_section_filters" data-group="post_sliders">
			<?php
			$bsf = $all['blog_section_filters'] ?? array();
			tolstenko_render_blog_section_defaults_fields( 'blog_section_filters', $bsf );
			?>
			<div class="row"><input type="text" name="tolstenko_block_defaults[blog_section_filters][btn_text]" value="<?php echo esc_attr( $bsf['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки под списком (Все статьи)"></div>
			<p class="description"><?php esc_html_e( 'Слайдер с фильтром по рубрикам blog_cat. Кнопка всегда ведёт на страницу блога.', 'tolstenko-theme' ); ?></p>
		</div>

		<div class="tolstenko-df-panel" data-panel="blog_section_tile" data-group="post_sliders">
			<?php
			$bst = $all['blog_section_tile'] ?? array();
			tolstenko_render_blog_section_defaults_fields( 'blog_section_tile', $bst );
			$bst_photo_id  = isset( $bst['sidebar_photo'] ) ? (int) $bst['sidebar_photo'] : 0;
			$bst_photo_url = $bst_photo_id ? wp_get_attachment_image_url( $bst_photo_id, 'medium' ) : '';
			?>
			<hr>
			<div class="muted"><?php esc_html_e( 'Сайдбар (карточка справа после первой статьи)', 'tolstenko-theme' ); ?></div>
			<p class="description"><?php esc_html_e( 'Пустые поля подставляются из «Шаблон вакансии → Контент» (сайдбар). Соцсети — из шапки/подвала.', 'tolstenko-theme' ); ?></p>
			<div class="row"><input type="text" name="tolstenko_block_defaults[blog_section_tile][sidebar_name]" value="<?php echo esc_attr( $bst['sidebar_name'] ?? '' ); ?>" style="width:100%" placeholder="Имя"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[blog_section_tile][sidebar_text]" rows="3" style="width:100%" placeholder="Текст"><?php echo esc_textarea( $bst['sidebar_text'] ?? '' ); ?></textarea></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[blog_section_tile][sidebar_btn]" value="<?php echo esc_attr( $bst['sidebar_btn'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[blog_section_tile][sidebar_btn_url]" value="<?php echo esc_attr( $bst['sidebar_btn_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка кнопки (пусто = модалка)"></div>
			<div class="row tolstenko-defaults-image-row">
				<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[blog_section_tile][sidebar_photo]" value="<?php echo (int) $bst_photo_id; ?>">
				<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Фото', 'tolstenko-theme' ); ?></button>
				<span class="icon-preview"><?php echo $bst_photo_url ? '<img src="' . esc_url( $bst_photo_url ) . '" style="max-height:80px">' : ''; ?></span>
			</div>
			<p class="description"><?php esc_html_e( 'Разметка как архив: первая статья крупно, сайдбар, остальные в сетке 3 колонки, пагинация.', 'tolstenko-theme' ); ?></p>
		</div>

		<div class="tolstenko-df-panel" data-panel="different_experiences" data-group="main">
			<div class="row"><input type="text" name="tolstenko_block_defaults[different_experiences][title]" value="<?php echo esc_attr( $de['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[different_experiences][text]" rows="3" placeholder="Текст"><?php echo esc_textarea( $de['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Пункты списка', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="different-experiences-list">
					<?php foreach ( (array) $de['items'] as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[different_experiences][items][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Текст пункта">
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions">
					<button type="button" class="button" data-add-item="different-experiences-list"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button>
				</div>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[different_experiences][tg_text]" value="<?php echo esc_attr( $de['tg_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки Telegram"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[different_experiences][tg_url]" value="<?php echo esc_attr( $de['tg_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка Telegram (пусто = из шапки/подвала)"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[different_experiences][modal_text]" value="<?php echo esc_attr( $de['modal_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки заявки"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[different_experiences][modal_url]" value="<?php echo esc_attr( $de['modal_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка заявки (пусто = модалка #modal)"></div>
		</div>

		<div class="tolstenko-df-panel" data-panel="partners" data-group="main">
			<div class="row"><input type="text" name="tolstenko_block_defaults[partners][title]" value="<?php echo esc_attr( $pr['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[partners][text]" rows="3" placeholder="Текст"><?php echo esc_textarea( $pr['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Логотипы партнёров', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="partners-list">
					<?php foreach ( (array) $pr['items'] as $idx => $it ) : ?>
						<?php
						$it = is_array( $it ) ? $it : array();
						$img_id = isset( $it['image'] ) ? (int) $it['image'] : 0;
						$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '';
						?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[partners][items][<?php echo (int) $idx; ?>][title]" value="<?php echo esc_attr( $it['title'] ?? '' ); ?>" placeholder="Название / alt">
								<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[partners][items][<?php echo (int) $idx; ?>][image]" value="<?php echo (int) $img_id; ?>">
								<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать логотип', 'tolstenko-theme' ); ?></button>
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="icon-preview" style="margin-top:8px;"><?php if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" alt=""><?php endif; ?></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions">
					<button type="button" class="button" data-add-item="partners-list"><?php esc_html_e( 'Добавить партнёра', 'tolstenko-theme' ); ?></button>
				</div>
			</div>
		</div>

		<?php
		$st  = $all['strategy'] ?? array();
		$tmc = $all['team_cards'] ?? array();
		$tgc = $all['tg_channel'] ?? array();
		$ts  = $all['three_steps'] ?? array();
		$doubts = $all['doubts'] ?? array();
		$familiar = $all['familiar'] ?? array();
		$result = $all['result'] ?? array();
		$faq = $all['faq'] ?? array();
		$seo_section = $all['seo_section'] ?? array();
		$faq_foto_id = isset( $faq['foto'] ) ? (int) $faq['foto'] : 0;
		$faq_foto_url = $faq_foto_id ? wp_get_attachment_image_url( $faq_foto_id, 'medium' ) : '';
		$st_img_id = isset( $st['image'] ) ? (int) $st['image'] : 0;
		$st_img_url = $st_img_id ? wp_get_attachment_image_url( $st_img_id, 'medium' ) : '';
		$st_mob_id = isset( $st['image_mob'] ) ? (int) $st['image_mob'] : 0;
		$st_mob_url = $st_mob_id ? wp_get_attachment_image_url( $st_mob_id, 'medium' ) : '';
		$tgc_img_id = isset( $tgc['image'] ) ? (int) $tgc['image'] : 0;
		$tgc_img_url = $tgc_img_id ? wp_get_attachment_image_url( $tgc_img_id, 'medium' ) : '';
		?>

		<div class="tolstenko-df-panel" data-panel="strategy" data-group="main">
			<div class="row"><input type="text" name="tolstenko_block_defaults[strategy][title]" value="<?php echo esc_attr( $st['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[strategy][subtitle]" value="<?php echo esc_attr( $st['subtitle'] ?? '' ); ?>" style="width:100%" placeholder="Подзаголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[strategy][text]" rows="3" placeholder="Текст"><?php echo esc_textarea( $st['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Пункты списка', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="strategy-list">
					<?php foreach ( (array) ( $st['items'] ?? array() ) as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[strategy][items][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Текст пункта">
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="strategy-list"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></div>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[strategy][btn_text]" value="<?php echo esc_attr( $st['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст основной кнопки"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[strategy][btn_url]" value="<?php echo esc_attr( $st['btn_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка основной кнопки (пусто = модалка)"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[strategy][file_text]" value="<?php echo esc_attr( $st['file_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки файла/шаблона"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[strategy][file_url]" value="<?php echo esc_attr( $st['file_url'] ?? '' ); ?>" style="width:100%" placeholder="URL файла"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[strategy][contacts_label]" value="<?php echo esc_attr( $st['contacts_label'] ?? '' ); ?>" style="width:100%" placeholder="Подпись контактов"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[strategy][phone]" value="<?php echo esc_attr( $st['phone'] ?? '' ); ?>" style="width:100%" placeholder="Телефон"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[strategy][telegram_text]" value="<?php echo esc_attr( $st['telegram_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки Telegram"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[strategy][telegram_url]" value="<?php echo esc_attr( $st['telegram_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка Telegram"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Изображение (desktop)', 'tolstenko-theme' ); ?></div>
				<div class="tolstenko-defaults-image-row">
					<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[strategy][image]" value="<?php echo (int) $st_img_id; ?>">
					<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать', 'tolstenko-theme' ); ?></button>
					<div class="icon-preview"><?php if ( $st_img_url ) : ?><img src="<?php echo esc_url( $st_img_url ); ?>" alt=""><?php endif; ?></div>
				</div>
			</div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Изображение (mobile)', 'tolstenko-theme' ); ?></div>
				<div class="tolstenko-defaults-image-row">
					<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[strategy][image_mob]" value="<?php echo (int) $st_mob_id; ?>">
					<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать', 'tolstenko-theme' ); ?></button>
					<div class="icon-preview"><?php if ( $st_mob_url ) : ?><img src="<?php echo esc_url( $st_mob_url ); ?>" alt=""><?php endif; ?></div>
				</div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="team_cards" data-group="main">
			<div class="row"><input type="text" name="tolstenko_block_defaults[team_cards][title]" value="<?php echo esc_attr( $tmc['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[team_cards][text]" rows="2" placeholder="Текст"><?php echo esc_textarea( $tmc['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Карточки команды (отдельно от CPT «Члены команды»)', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="team-cards-list">
					<?php foreach ( (array) ( $tmc['items'] ?? array() ) as $idx => $it ) : ?>
						<?php
						$it = is_array( $it ) ? $it : array();
						$img_id = isset( $it['image'] ) ? (int) $it['image'] : 0;
						$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '';
						?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[team_cards][items][<?php echo (int) $idx; ?>][name]" value="<?php echo esc_attr( $it['name'] ?? '' ); ?>" placeholder="Имя">
								<input type="text" name="tolstenko_block_defaults[team_cards][items][<?php echo (int) $idx; ?>][position]" value="<?php echo esc_attr( $it['position'] ?? '' ); ?>" placeholder="Должность">
								<input type="text" name="tolstenko_block_defaults[team_cards][items][<?php echo (int) $idx; ?>][exp]" value="<?php echo esc_attr( $it['exp'] ?? '' ); ?>" placeholder="Опыт">
							</div>
							<div class="row"><textarea name="tolstenko_block_defaults[team_cards][items][<?php echo (int) $idx; ?>][text]" rows="2" placeholder="Описание"><?php echo esc_textarea( $it['text'] ?? '' ); ?></textarea></div>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[team_cards][items][<?php echo (int) $idx; ?>][btn_text]" value="<?php echo esc_attr( $it['btn_text'] ?? '' ); ?>" placeholder="Текст кнопки">
								<input type="url" name="tolstenko_block_defaults[team_cards][items][<?php echo (int) $idx; ?>][btn_url]" value="<?php echo esc_attr( $it['btn_url'] ?? '' ); ?>" placeholder="Ссылка кнопки">
								<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[team_cards][items][<?php echo (int) $idx; ?>][image]" value="<?php echo (int) $img_id; ?>">
								<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Фото', 'tolstenko-theme' ); ?></button>
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="icon-preview" style="margin-top:8px;"><?php if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" alt=""><?php endif; ?></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="team-cards-list"><?php esc_html_e( 'Добавить карточку', 'tolstenko-theme' ); ?></button></div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="tg_channel">
			<div class="row"><input type="text" name="tolstenko_block_defaults[tg_channel][title]" value="<?php echo esc_attr( $tgc['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[tg_channel][text]" rows="3" placeholder="Текст"><?php echo esc_textarea( $tgc['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Пункты списка', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="tg-channel-list">
					<?php foreach ( (array) ( $tgc['items'] ?? array() ) as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[tg_channel][items][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Текст пункта">
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="tg-channel-list"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></div>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[tg_channel][btn_text]" value="<?php echo esc_attr( $tgc['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[tg_channel][btn_url]" value="<?php echo esc_attr( $tgc['btn_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка (пусто = Telegram из шапки)"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Изображение', 'tolstenko-theme' ); ?></div>
				<div class="tolstenko-defaults-image-row">
					<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[tg_channel][image]" value="<?php echo (int) $tgc_img_id; ?>">
					<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать', 'tolstenko-theme' ); ?></button>
					<div class="icon-preview"><?php if ( $tgc_img_url ) : ?><img src="<?php echo esc_url( $tgc_img_url ); ?>" alt=""><?php endif; ?></div>
				</div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="three_steps" data-group="main">
			<div class="row"><input type="text" name="tolstenko_block_defaults[three_steps][title]" value="<?php echo esc_attr( $ts['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[three_steps][text]" rows="3" placeholder="Текст"><?php echo esc_textarea( $ts['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Шаги', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="three-steps-list">
					<?php foreach ( (array) ( $ts['items'] ?? array() ) as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[three_steps][items][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Текст шага">
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="three-steps-list"><?php esc_html_e( 'Добавить шаг', 'tolstenko-theme' ); ?></button></div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="doubts" data-group="main">
			<div class="row"><input type="text" name="tolstenko_block_defaults[doubts][subtitle]" value="<?php echo esc_attr( $doubts['subtitle'] ?? '' ); ?>" style="width:100%" placeholder="Подзаголовок"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[doubts][title]" value="<?php echo esc_attr( $doubts['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Карточки возражений', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="doubts-list">
					<?php foreach ( (array) ( $doubts['items'] ?? array() ) as $idx => $it ) : ?>
						<?php $it = is_array( $it ) ? $it : array(); ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[doubts][items][<?php echo (int) $idx; ?>][badge]" value="<?php echo esc_attr( $it['badge'] ?? '' ); ?>" placeholder="Бейдж (Цена)">
								<input type="text" name="tolstenko_block_defaults[doubts][items][<?php echo (int) $idx; ?>][title]" value="<?php echo esc_attr( $it['title'] ?? '' ); ?>" placeholder="Заголовок возражения" style="flex:1">
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="row"><textarea name="tolstenko_block_defaults[doubts][items][<?php echo (int) $idx; ?>][text]" rows="3" placeholder="Текст ответа"><?php echo esc_textarea( $it['text'] ?? '' ); ?></textarea></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="doubts-list"><?php esc_html_e( 'Добавить карточку', 'tolstenko-theme' ); ?></button></div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="familiar" data-group="main">
			<div class="row"><input type="text" name="tolstenko_block_defaults[familiar][subtitle]" value="<?php echo esc_attr( $familiar['subtitle'] ?? '' ); ?>" style="width:100%" placeholder="Подзаголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[familiar][title]" rows="2" style="width:100%" placeholder="Заголовок (HTML, span для акцента)"><?php echo esc_textarea( $familiar['title'] ?? '' ); ?></textarea></div>
			<div class="row"><textarea name="tolstenko_block_defaults[familiar][text]" rows="2" style="width:100%" placeholder="Текст под заголовком"><?php echo esc_textarea( $familiar['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Карточки', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="familiar-list">
					<?php foreach ( (array) ( $familiar['items'] ?? array() ) as $idx => $it ) : ?>
						<?php $it = is_array( $it ) ? $it : array(); ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[familiar][items][<?php echo (int) $idx; ?>][title]" value="<?php echo esc_attr( $it['title'] ?? '' ); ?>" placeholder="Заголовок" style="flex:1">
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="row"><textarea name="tolstenko_block_defaults[familiar][items][<?php echo (int) $idx; ?>][text]" rows="2" placeholder="Текст"><?php echo esc_textarea( $it['text'] ?? '' ); ?></textarea></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="familiar-list"><?php esc_html_e( 'Добавить карточку', 'tolstenko-theme' ); ?></button></div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="result" data-group="main">
			<div class="row"><input type="text" name="tolstenko_block_defaults[result][subtitle]" value="<?php echo esc_attr( $result['subtitle'] ?? '' ); ?>" style="width:100%" placeholder="Подзаголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[result][title]" rows="2" style="width:100%" placeholder="Заголовок (HTML, span для акцента)"><?php echo esc_textarea( $result['title'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Карточки гарантий', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="result-list">
					<?php foreach ( (array) ( $result['items'] ?? array() ) as $idx => $it ) : ?>
						<?php
						$it = is_array( $it ) ? $it : array();
						$ico_id = isset( $it['ico'] ) ? (int) $it['ico'] : 0;
						$ico_url = $ico_id ? wp_get_attachment_image_url( $ico_id, 'thumbnail' ) : '';
						?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[result][items][<?php echo (int) $idx; ?>][title]" value="<?php echo esc_attr( $it['title'] ?? '' ); ?>" placeholder="Заголовок" style="flex:1">
								<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[result][items][<?php echo (int) $idx; ?>][ico]" value="<?php echo (int) $ico_id; ?>">
								<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Иконка', 'tolstenko-theme' ); ?></button>
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="icon-preview" style="margin-top:8px;"><?php if ( $ico_url ) : ?><img src="<?php echo esc_url( $ico_url ); ?>" alt=""><?php endif; ?></div>
							<div class="row"><textarea name="tolstenko_block_defaults[result][items][<?php echo (int) $idx; ?>][text]" rows="2" placeholder="Текст"><?php echo esc_textarea( $it['text'] ?? '' ); ?></textarea></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="result-list"><?php esc_html_e( 'Добавить карточку', 'tolstenko-theme' ); ?></button></div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="faq">
			<div class="row"><input type="text" name="tolstenko_block_defaults[faq][title]" value="<?php echo esc_attr( $faq['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[faq][text]" rows="3" placeholder="Текст под заголовком"><?php echo esc_textarea( $faq['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Вопросы и ответы', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="faq-list">
					<?php foreach ( (array) ( $faq['items'] ?? array() ) as $idx => $it ) : ?>
						<?php $it = is_array( $it ) ? $it : array( 'title' => '', 'redactor' => '' ); ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[faq][items][<?php echo (int) $idx; ?>][title]" value="<?php echo esc_attr( $it['title'] ?? '' ); ?>" placeholder="Вопрос" style="width:100%">
								<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
								<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="row tolstenko-faq-editor-wrap">
								<p class="description" style="margin:0 0 6px;"><?php esc_html_e( 'Ответ', 'tolstenko-theme' ); ?></p>
								<?php tolstenko_faq_answer_editor( (string) ( $it['redactor'] ?? '' ), (int) $idx ); ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="faq-list"><?php esc_html_e( 'Добавить вопрос', 'tolstenko-theme' ); ?></button></div>
			</div>
			<hr>
			<div class="muted"><?php esc_html_e( 'Форма справа', 'tolstenko-theme' ); ?></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[faq][form_title]" value="<?php echo esc_attr( $faq['form_title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок формы"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[faq][form_text]" rows="2" placeholder="Текст формы"><?php echo esc_textarea( $faq['form_text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Фото справа', 'tolstenko-theme' ); ?></div>
				<div class="tolstenko-defaults-image-row">
					<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[faq][foto]" value="<?php echo (int) $faq_foto_id; ?>">
					<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать', 'tolstenko-theme' ); ?></button>
					<div class="icon-preview"><?php if ( $faq_foto_url ) : ?><img src="<?php echo esc_url( $faq_foto_url ); ?>" alt=""><?php endif; ?></div>
				</div>
			</div>
			<div class="row"><textarea name="tolstenko_block_defaults[faq][foto_text]" rows="2" placeholder="Текст рядом с фото (HTML)"><?php echo esc_textarea( $faq['foto_text'] ?? '' ); ?></textarea></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[faq][phone]" value="<?php echo esc_attr( $faq['phone'] ?? '' ); ?>" style="width:100%" placeholder="Телефон (пусто = из шапки)"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[faq][telegram_url]" value="<?php echo esc_attr( $faq['telegram_url'] ?? '' ); ?>" style="width:100%" placeholder="Telegram URL (пусто = из шапки)"></div>
		</div>

		<div class="tolstenko-df-panel" data-panel="seo_section" data-group="main">
			<p class="description"><?php esc_html_e( 'Заголовок, подзаголовок и текст кнопки «Читать далее». Блоки содержимого настраиваются в редакторе записи (блок «SEO продвижение»).', 'tolstenko-theme' ); ?></p>
			<div class="row"><input type="text" name="tolstenko_block_defaults[seo_section][title]" value="<?php echo esc_attr( $seo_section['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[seo_section][subtitle]" rows="3" placeholder="Подзаголовок"><?php echo esc_textarea( $seo_section['subtitle'] ?? '' ); ?></textarea></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[seo_section][more_text]" value="<?php echo esc_attr( $seo_section['more_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки раскрытия (по умолчанию: Читать далее)"></div>
		</div>

		</div><!-- .tolstenko-df-panels-source -->
	</div>
	<div class="actions">
		<!-- <button type="submit" name="tolstenko_defaults_reset_all" value="1" class="button"><?php esc_html_e( 'Сбросить всё к заводским', 'tolstenko-theme' ); ?></button> -->
	</div>
	<?php submit_button( __( 'Сохранить дефолты', 'tolstenko-theme' ) ); ?>
	<script>
	(function(){
		var root = document.querySelector('.tolstenko-df');
		if (!root) return;

		var panelGroupMap = {
			main_hero: 'main',
			free_audit: 'main',
			solution: 'main',
			consultation_tel: 'main',
			one_team: 'main',
			actions: 'main',
			consultation_tg: 'main',
			different_experiences: 'main',
			three_steps: 'main',
			doubts: 'main',
			familiar: 'main',
			result: 'main',
			strategy: 'main',
			author: 'main',
			team_cards: 'main',
			partners: 'main',
			certificates: 'main',
			guide_banner: 'main',
			timed_modal: 'main',
			city: 'main',
			consultation_whatsapp: 'main',
			consultation_free: 'main',
			tg_channel: 'main',
			faq: 'main',
			seo_section: 'main',
			case_section: 'post_sliders',
			reviews: 'post_sliders',
			service_section: 'post_sliders',
			service_section_filters: 'post_sliders',
			service_section_tile: 'post_sliders',
			blog_section: 'post_sliders',
			blog_section_filters: 'post_sliders',
			blog_section_tile: 'post_sliders',
			actions_section: 'post_sliders',
			vacancies_banner: 'vacancies',
			vacancies_section: 'vacancies'
		};

		// Разложить панели по группам
		root.querySelectorAll('.tolstenko-df-panel').forEach(function(panel){
			var key = panel.getAttribute('data-panel');
			var group = panel.getAttribute('data-group') || panelGroupMap[key];
			if (!group) return;
			var slot = root.querySelector('.tolstenko-df-group-panels[data-group-panels="' + group + '"]');
			if (slot) slot.appendChild(panel);
		});
		var source = root.querySelector('.tolstenko-df-panels-source');
		if (source) source.remove();

		// Функция переиндексации элементов репитера
		function reindexRepeater(listEl, namePrefix) {
			var items = listEl.querySelectorAll('[data-repeater-item]');
			items.forEach(function(item, idx){
				var inputs = item.querySelectorAll('[name]');
				inputs.forEach(function(input){
					var name = input.getAttribute('name');
					var regex = new RegExp('\\' + namePrefix + '\\[\\d+\\]');
					if (regex.test(name)) {
						input.setAttribute('name', name.replace(regex, namePrefix + '[' + idx + ']'));
					}
				});
				// Обновляем data-атрибуты для кнопок перемещения
				var upBtn = item.querySelector('[data-move-up]');
				var downBtn = item.querySelector('[data-move-down]');
				if (upBtn) upBtn.dataset.index = idx;
				if (downBtn) downBtn.dataset.index = idx;
			});
		}

		// Функция перемещения элемента
		function moveItem(item, direction) {
			var list = item.parentElement;
			var items = list.querySelectorAll('[data-repeater-item]');
			var index = Array.from(items).indexOf(item);
			var newIndex = index + direction;
			if (newIndex < 0 || newIndex >= items.length) return;
			
			var namePrefix = '';
			var nameInputs = item.querySelectorAll('[name]');
			if (nameInputs.length > 0) {
				var name = nameInputs[0].getAttribute('name');
				var match = name.match(/^([^\[]+\[[^\]]+\])\[/);
				if (match) namePrefix = match[1];
			}
			if (!namePrefix) {
				// Пробуем найти через data-repeater-list
				var listKey = list.getAttribute('data-repeater-list');
				if (listKey) {
					var panel = list.closest('.tolstenko-df-panel');
					if (panel) {
						var panelKey = panel.getAttribute('data-panel');
						if (panelKey) {
							namePrefix = panelKey + '[' + listKey.replace(/-list$/, '') + ']';
						}
					}
				}
			}
			
			if (newIndex > index) {
				item.parentElement.insertBefore(item, items[newIndex + 1] || null);
			} else {
				item.parentElement.insertBefore(item, items[newIndex]);
			}
			
			if (namePrefix) {
				reindexRepeater(list, namePrefix);
			}
		}

		// Обработка кликов по кнопкам перемещения и удаления
		document.addEventListener('click', function(e){
			var target = e.target;
			if (target.closest && target.closest('[data-move-up]')) {
				e.preventDefault();
				var btn = target.closest('[data-move-up]');
				var item = btn.closest('[data-repeater-item]');
				if (item) moveItem(item, -1);
				return;
			}
			if (target.closest && target.closest('[data-move-down]')) {
				e.preventDefault();
				var btn = target.closest('[data-move-down]');
				var item = btn.closest('[data-repeater-item]');
				if (item) moveItem(item, 1);
				return;
			}
			var removeBtn = target.closest && target.closest('[data-remove-item]');
			if (removeBtn) {
				e.preventDefault();
				var item = removeBtn.closest('[data-repeater-item]');
				if (!item) return;
				var area = item.querySelector('textarea.wp-editor-area');
				if (area && area.id && window.wp && wp.editor && typeof wp.editor.remove === 'function') {
					try { wp.editor.remove(area.id); } catch (err) {}
				}
				item.remove();
				return;
			}
			var addBtn = target.closest && target.closest('[data-add-item]');
			if (addBtn) {
				e.preventDefault();
				var key = addBtn.getAttribute('data-add-item');
				var panel = addBtn.closest('.tolstenko-df-panel') || document;
				var list = panel.querySelector('[data-repeater-list="' + key + '"]') || document.querySelector('[data-repeater-list="' + key + '"]');
				if (!list) return;
				var directItems = list.querySelectorAll('[data-repeater-item]');
				var idx = directItems.length;
				var html = '';
				if (key === 'main-hero-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><textarea name="tolstenko_block_defaults[main_hero][items][' + idx + ']" rows="2" style="width:100%"></textarea><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'reviews-cards-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[reviews][cards][' + idx + '][title]" placeholder="Название (Яндекс, 2ГИС…)" style="flex:1"><input type="url" name="tolstenko_block_defaults[reviews][cards][' + idx + '][url]" placeholder="Ссылка" style="flex:1"><input type="number" min="1" max="5" name="tolstenko_block_defaults[reviews][cards][' + idx + '][rating]" value="5" style="width:80px" title="Рейтинг"><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'certificates-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[certificates][items][' + idx + '][title]" placeholder="Подпись / alt"><input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[certificates][items][' + idx + '][image]" value="0"><button type="button" class="button tolstenko-defaults-pick-icon">Выбрать изображение</button><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="icon-preview cert-preview" style="margin-top:8px;"></div></div>';
				} else if (key === 'free-audit-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[free_audit][items][' + idx + ']" placeholder="Текст пункта"><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'solution-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><textarea name="tolstenko_block_defaults[solution][items][' + idx + ']" rows="2" style="width:100%" placeholder="Текст пункта (HTML)"></textarea><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'solution-list-second') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><textarea name="tolstenko_block_defaults[solution][items_second][' + idx + ']" rows="2" style="width:100%" placeholder="Текст пункта (HTML)"></textarea><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'one-team-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[one_team][items][' + idx + '][value]" placeholder="Значение (10+)"><input type="text" name="tolstenko_block_defaults[one_team][items][' + idx + '][text]" placeholder="Подпись"><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'author-list' || key === 'author-sublist') {
					var ak = key === 'author-list' ? 'list' : 'sublist';
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[author][' + ak + '][' + idx + '][text]" placeholder="Пункт списка" style="width:100%"><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'author-items') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[author][items][' + idx + '][value]" placeholder="Значение"><input type="text" name="tolstenko_block_defaults[author][items][' + idx + '][text]" placeholder="Подпись"><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'author-links') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[author][links][' + idx + '][title]" placeholder="Текст ссылки"><input type="url" name="tolstenko_block_defaults[author][links][' + idx + '][url]" placeholder="URL"><input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[author][links][' + idx + '][icon]" value="0"><button type="button" class="button tolstenko-defaults-pick-icon">Иконка</button><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="icon-preview" style="margin-top:8px;"></div></div>';
				} else if (key === 'author-speeches') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[author][speeches][' + idx + '][text]" placeholder="Подпись" style="flex:1 1 220px"><input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[author][speeches][' + idx + '][image]" value="0"><button type="button" class="button tolstenko-defaults-pick-icon">Фото</button><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="icon-preview" style="margin-top:8px;"></div></div>';
				} else if (key === 'different-experiences-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[different_experiences][items][' + idx + ']" placeholder="Текст пункта"><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'partners-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[partners][items][' + idx + '][title]" placeholder="Название / alt"><input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[partners][items][' + idx + '][image]" value="0"><button type="button" class="button tolstenko-defaults-pick-icon">Выбрать логотип</button><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="icon-preview" style="margin-top:8px;"></div></div>';
				} else if (key === 'strategy-list' || key === 'tg-channel-list' || key === 'three-steps-list') {
					var map = { 'strategy-list': 'strategy', 'tg-channel-list': 'tg_channel', 'three-steps-list': 'three_steps' };
					var k = map[key];
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[' + k + '][items][' + idx + ']" placeholder="Текст"><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'doubts-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[doubts][items][' + idx + '][badge]" placeholder="Бейдж (Цена)"><input type="text" name="tolstenko_block_defaults[doubts][items][' + idx + '][title]" placeholder="Заголовок возражения" style="flex:1"><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="row"><textarea name="tolstenko_block_defaults[doubts][items][' + idx + '][text]" rows="3" placeholder="Текст ответа"></textarea></div></div>';
				} else if (key === 'familiar-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[familiar][items][' + idx + '][title]" placeholder="Заголовок" style="flex:1"><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="row"><textarea name="tolstenko_block_defaults[familiar][items][' + idx + '][text]" rows="2" placeholder="Текст"></textarea></div></div>';
				} else if (key === 'result-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[result][items][' + idx + '][title]" placeholder="Заголовок" style="flex:1"><input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[result][items][' + idx + '][ico]" value="0"><button type="button" class="button tolstenko-defaults-pick-icon">Иконка</button><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="icon-preview" style="margin-top:8px;"></div><div class="row"><textarea name="tolstenko_block_defaults[result][items][' + idx + '][text]" rows="2" placeholder="Текст"></textarea></div></div>';
				} else if (key === 'actions-list') {
					var actionOpts = <?php
					$opt_html = '<option value="0">— Без ссылки —</option>';
					foreach ( $action_posts as $ap ) {
						$opt_html .= '<option value="' . (int) $ap->ID . '">' . esc_html( get_the_title( $ap ) ) . '</option>';
					}
					echo wp_json_encode( $opt_html );
					?>;
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[actions][items][' + idx + '][type]" placeholder="Тип / метка"><input type="text" name="tolstenko_block_defaults[actions][items][' + idx + '][title]" placeholder="Заголовок карточки"></div><div class="row"><textarea name="tolstenko_block_defaults[actions][items][' + idx + '][text]" rows="2" placeholder="Текст карточки"></textarea></div><div class="cols"><select name="tolstenko_block_defaults[actions][items][' + idx + '][action_id]" style="min-width:220px">' + actionOpts + '</select><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'team-cards-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[team_cards][items][' + idx + '][name]" placeholder="Имя"><input type="text" name="tolstenko_block_defaults[team_cards][items][' + idx + '][position]" placeholder="Должность"><input type="text" name="tolstenko_block_defaults[team_cards][items][' + idx + '][exp]" placeholder="Опыт"></div><div class="row"><textarea name="tolstenko_block_defaults[team_cards][items][' + idx + '][text]" rows="2" placeholder="Описание"></textarea></div><div class="cols"><input type="text" name="tolstenko_block_defaults[team_cards][items][' + idx + '][btn_text]" placeholder="Текст кнопки"><input type="url" name="tolstenko_block_defaults[team_cards][items][' + idx + '][btn_url]" placeholder="Ссылка кнопки"><input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[team_cards][items][' + idx + '][image]" value="0"><button type="button" class="button tolstenko-defaults-pick-icon">Фото</button><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="icon-preview" style="margin-top:8px;"></div></div>';
				} else if (key === 'faq-list') {
					var editorId = 'tolstenko_faq_redactor_' + idx + '_' + Date.now();
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[faq][items][' + idx + '][title]" placeholder="Вопрос" style="width:100%"><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="row tolstenko-faq-editor-wrap"><p class="description" style="margin:0 0 6px;">Ответ</p><textarea id="' + editorId + '" name="tolstenko_block_defaults[faq][items][' + idx + '][redactor]" rows="8" class="wp-editor-area"></textarea></div></div>';
					list.insertAdjacentHTML('beforeend', html);
					if (window.wp && wp.editor && typeof wp.editor.initialize === 'function') {
						wp.editor.initialize(editorId, {
							tinymce: {
								wpautop: true,
								plugins: 'charmap colorpicker hr lists paste tabfocus textcolor wordpress wpautoresize wpeditimage wpemoji wpgallery wplink wptextpattern',
								toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_adv',
								toolbar2: 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo'
							},
							quicktags: true,
							mediaButtons: true
						});
					}
					html = '';
				}
				if (html) list.insertAdjacentHTML('beforeend', html);
			}
		});

		function refreshFaqEditors(){
			if (typeof tinymce === 'undefined') return;
			window.setTimeout(function(){
				var editors = tinymce.editors || [];
				for (var i = 0; i < editors.length; i++) {
					var ed = editors[i];
					if (!ed || !ed.id || ed.id.indexOf('tolstenko_faq_redactor_') !== 0) continue;
					try {
						ed.fire('show');
						if (ed.theme && typeof ed.theme.resizeTo === 'function') {
							ed.theme.resizeTo('100%', 180);
						}
					} catch (err) {}
				}
			}, 60);
		}

		function activateTab(tab){
			if (!tab) return;
			var target = tab.getAttribute('data-panel');
			var group = tab.getAttribute('data-group') || panelGroupMap[target] || '';
			root.querySelectorAll('.tolstenko-df-tab').forEach(function(t){ t.classList.remove('active'); });
			root.querySelectorAll('.tolstenko-df-panel').forEach(function(p){ p.classList.remove('active'); });
			root.querySelectorAll('.tolstenko-df-tabs-group').forEach(function(g){ g.classList.remove('is-active'); });
			tab.classList.add('active');
			var panel = root.querySelector('.tolstenko-df-panel[data-panel="' + target + '"]');
			if (panel) panel.classList.add('active');
			var groupEl = root.querySelector('.tolstenko-df-tabs-group[data-group="' + group + '"]');
			if (groupEl) {
				groupEl.classList.add('is-active');
				groupEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
			}
			if (target === 'faq') refreshFaqEditors();
		}

		root.querySelectorAll('.tolstenko-df-tab').forEach(function(tab){
			tab.addEventListener('click', function(){
				activateTab(tab);
			});
		});

		var initial = root.querySelector('.tolstenko-df-tab.active') || root.querySelector('.tolstenko-df-tab');
		activateTab(initial);

		var defaultsForm = root.closest('form');
		if (defaultsForm) {
			defaultsForm.addEventListener('submit', function(){
				if (typeof tinymce !== 'undefined' && typeof tinymce.triggerSave === 'function') {
					tinymce.triggerSave();
				}
			});
		}

		function bindIconPicker(scope){
			scope.querySelectorAll('.tolstenko-defaults-pick-icon').forEach(function(btn){
				if (btn.dataset.bound) return;
				btn.dataset.bound = '1';
				btn.addEventListener('click', function(ev){
					ev.preventDefault();
					if (typeof wp === 'undefined' || !wp.media) return;
					var row = btn.closest('[data-repeater-item], .tolstenko-defaults-image-row');
					if (!row) return;
					var input = row.querySelector('.tolstenko-defaults-icon-id');
					var preview = row.querySelector('.icon-preview');
					var frame = wp.media({ title: 'Выберите иконку', button: { text: 'Использовать' }, multiple: false, library: { type: 'image' } });
					frame.on('select', function(){
						var sel = frame.state().get('selection').first();
						if (!sel) return;
						var json = sel.toJSON();
						input.value = json.id || 0;
						var img = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) || json.url || '';
						preview.innerHTML = img ? '<img src="' + img + '" alt="">' : '';
					});
					frame.open();
				});
			});
		}
		bindIconPicker(document);
		document.addEventListener('click', function(e){
			var btn = e.target;
			if (btn.matches && btn.matches('[data-add-item]')) {
				setTimeout(function(){ bindIconPicker(document); }, 0);
			}
		});

		function bindVideoPicker(scope){
			scope.querySelectorAll('.tolstenko-defaults-pick-video').forEach(function(btn){
				if (btn.dataset.bound) return;
				btn.dataset.bound = '1';
				btn.addEventListener('click', function(ev){
					ev.preventDefault();
					if (typeof wp === 'undefined' || !wp.media) return;
					var row = btn.closest('.tolstenko-defaults-image-row');
					if (!row) return;
					var input = row.querySelector('.tolstenko-defaults-icon-id');
					var preview = row.querySelector('.icon-preview');
					var frame = wp.media({
						title: 'Выберите видео',
						button: { text: 'Использовать' },
						multiple: false,
						library: { type: 'video' }
					});
					frame.on('select', function(){
						var sel = frame.state().get('selection').first();
						if (!sel) return;
						var json = sel.toJSON();
						input.value = json.id || 0;
						var name = (json.filename || json.title || json.url || '');
						if (name && name.indexOf('/') !== -1) {
							name = name.split('/').pop();
						}
						preview.textContent = name || '';
					});
					frame.open();
				});
			});
		}
		bindVideoPicker(document);
	})();
	</script>
	</form>
	</div>
	<?php
}

function tolstenko_save_block_defaults_from_request() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['tolstenko_defaults_reset_all'] ) ) {
		delete_option( 'tolstenko_block_defaults' );
		return;
	}
	$raw = isset( $_POST['tolstenko_block_defaults'] ) ? wp_unslash( $_POST['tolstenko_block_defaults'] ) : array();
	$out = array();
	if ( ! is_array( $raw ) ) {
		$raw = array();
	}

	$out['main_hero'] = array(
		'title'           => tolstenko_kses_html( $raw['main_hero']['title'] ?? '' ),
		'text'            => tolstenko_kses_html( $raw['main_hero']['text'] ?? '' ),
		'btn_text'        => tolstenko_kses_html( $raw['main_hero']['btn_text'] ?? '' ),
		'show_promo'      => ! empty( $raw['main_hero']['show_promo'] ),
		'promo_text'      => tolstenko_kses_html( $raw['main_hero']['promo_text'] ?? '' ),
		'present_image'   => isset( $raw['main_hero']['present_image'] ) ? (int) $raw['main_hero']['present_image'] : 0,
		'person_name'     => sanitize_text_field( $raw['main_hero']['person_name'] ?? '' ),
		'person_position' => sanitize_text_field( $raw['main_hero']['person_position'] ?? '' ),
		'image'           => isset( $raw['main_hero']['image'] ) ? (int) $raw['main_hero']['image'] : 0,
		'items'           => array(),
	);
	if ( isset( $raw['main_hero']['items'] ) && is_array( $raw['main_hero']['items'] ) ) {
		foreach ( $raw['main_hero']['items'] as $v ) {
			$v = trim( (string) $v );
			if ( $v !== '' ) {
				$out['main_hero']['items'][] = tolstenko_kses_html( $v );
			}
		}
	}

	$out['guide_banner'] = array(
		'enabled'  => ! empty( $raw['guide_banner']['enabled'] ),
		'text'     => sanitize_text_field( $raw['guide_banner']['text'] ?? '' ),
		'btn_text' => sanitize_text_field( $raw['guide_banner']['btn_text'] ?? '' ),
		'btn_url'  => esc_url_raw( $raw['guide_banner']['btn_url'] ?? '' ),
	);

	$tm_delay = isset( $raw['timed_modal']['delay_seconds'] ) ? (int) $raw['timed_modal']['delay_seconds'] : 40;
	if ( $tm_delay < 5 ) {
		$tm_delay = 5;
	}
	if ( $tm_delay > 600 ) {
		$tm_delay = 600;
	}
	$out['timed_modal'] = array(
		'enabled'       => ! empty( $raw['timed_modal']['enabled'] ),
		'delay_seconds' => $tm_delay,
		'title'         => sanitize_text_field( $raw['timed_modal']['title'] ?? '' ),
		'text'          => sanitize_textarea_field( $raw['timed_modal']['text'] ?? '' ),
		'phone'         => sanitize_text_field( $raw['timed_modal']['phone'] ?? '' ),
	);

	$out['reviews'] = array(
		'title'      => tolstenko_kses_html( $raw['reviews']['title'] ?? '' ),
		'text'       => tolstenko_kses_html( $raw['reviews']['text'] ?? '' ),
		'show_items' => ! empty( $raw['reviews']['show_items'] ),
		'ids'        => tolstenko_sanitize_ids( $raw['reviews']['ids'] ?? array() ),
		'cards'      => array(),
	);
	if ( isset( $raw['reviews']['cards'] ) && is_array( $raw['reviews']['cards'] ) ) {
		foreach ( $raw['reviews']['cards'] as $card ) {
			if ( ! is_array( $card ) ) {
				continue;
			}
			$c_title = sanitize_text_field( $card['title'] ?? '' );
			$c_url   = esc_url_raw( $card['url'] ?? '' );
			$c_rating = isset( $card['rating'] ) ? (int) $card['rating'] : 5;
			if ( $c_rating < 1 ) {
				$c_rating = 5;
			}
			if ( $c_rating > 5 ) {
				$c_rating = 5;
			}
			if ( $c_title === '' ) {
				continue;
			}
			$out['reviews']['cards'][] = array(
				'title'  => $c_title,
				'url'    => $c_url,
				'rating' => $c_rating,
			);
		}
	}

	$out['certificates']['title'] = tolstenko_kses_html( $raw['certificates']['title'] ?? ''  );
	$out['certificates']['text']  = tolstenko_kses_html( $raw['certificates']['text'] ?? ''  );
	$out['certificates']['items'] = array();
	if ( isset( $raw['certificates']['items'] ) && is_array( $raw['certificates']['items'] ) ) {
		foreach ( $raw['certificates']['items'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$title = tolstenko_kses_html( $it['title'] ?? ''  );
			$image = isset( $it['image'] ) ? (int) $it['image'] : 0;
			if ( $title === '' && $image === 0 ) {
				continue;
			}
			$out['certificates']['items'][] = array(
				'title' => $title,
				'image' => $image,
			);
		}
	}

	$out['actions'] = array(
		'title' => tolstenko_kses_html( $raw['actions']['title'] ?? '' ),
		'items' => array(),
	);
	if ( ! empty( $raw['actions']['items'] ) && is_array( $raw['actions']['items'] ) ) {
		foreach ( $raw['actions']['items'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$type = sanitize_text_field( $it['type'] ?? '' );
			$t    = tolstenko_kses_html( $it['title'] ?? '' );
			$text = tolstenko_kses_html( $it['text'] ?? '' );
			$aid  = isset( $it['action_id'] ) ? (int) $it['action_id'] : 0;
			if ( $type === '' && $t === '' && $text === '' ) {
				continue;
			}
			if ( $aid > 0 && get_post_type( $aid ) !== 'actions' ) {
				$aid = 0;
			}
			$out['actions']['items'][] = array(
				'type'      => $type,
				'title'     => $t,
				'text'      => $text,
				'action_id' => $aid,
			);
			if ( count( $out['actions']['items'] ) >= 4 ) {
				break;
			}
		}
	}

	$out['actions_section']['title'] = tolstenko_kses_html( $raw['actions_section']['title'] ?? ''  );
	$out['actions_section']['text']  = tolstenko_kses_html( $raw['actions_section']['text'] ?? ''  );

	$out['city']['title'] = tolstenko_kses_html( $raw['city']['title'] ?? ''  );
	$out['city']['text']  = tolstenko_kses_html( $raw['city']['text'] ?? ''  );

	$out['vacancies_banner'] = array(
		'title' => tolstenko_kses_html( $raw['vacancies_banner']['title'] ?? ''  ),
		'text'  => tolstenko_kses_html( $raw['vacancies_banner']['text'] ?? ''  ),
		'image' => isset( $raw['vacancies_banner']['image'] ) ? (int) $raw['vacancies_banner']['image'] : 0,
	);
	$out['vacancies_section'] = array(
		'title' => tolstenko_kses_html( $raw['vacancies_section']['title'] ?? ''  ),
		'text'  => tolstenko_kses_html( $raw['vacancies_section']['text'] ?? ''  ),
	);

	$out['case_section'] = array(
		'title'          => tolstenko_kses_html( $raw['case_section']['title'] ?? '' ),
		'text'           => tolstenko_kses_html( $raw['case_section']['text'] ?? '' ),
		'posts_per_page' => isset( $raw['case_section']['posts_per_page'] ) ? (int) $raw['case_section']['posts_per_page'] : 4,
		'ids'            => tolstenko_sanitize_ids( $raw['case_section']['ids'] ?? array() ),
	);

	$out['service_section'] = array(
		'title'          => tolstenko_kses_html( $raw['service_section']['title'] ?? '' ),
		'text'           => tolstenko_kses_html( $raw['service_section']['text'] ?? '' ),
		'posts_per_page' => isset( $raw['service_section']['posts_per_page'] ) ? (int) $raw['service_section']['posts_per_page'] : 6,
		'ids'            => tolstenko_sanitize_service_section_ids( $raw['service_section']['ids'] ?? array() ),
	);

	$out['service_section_filters'] = array(
		'title'          => tolstenko_kses_html( $raw['service_section_filters']['title'] ?? '' ),
		'text'           => tolstenko_kses_html( $raw['service_section_filters']['text'] ?? '' ),
		'posts_per_page' => isset( $raw['service_section_filters']['posts_per_page'] ) ? (int) $raw['service_section_filters']['posts_per_page'] : 6,
		'ids'            => tolstenko_sanitize_service_section_ids( $raw['service_section_filters']['ids'] ?? array() ),
	);

	$out['service_section_tile'] = array(
		'title' => tolstenko_kses_html( $raw['service_section_tile']['title'] ?? '' ),
		'text'  => tolstenko_kses_html( $raw['service_section_tile']['text'] ?? '' ),
	);

	$out['blog_section'] = array(
		'title'          => tolstenko_kses_html( $raw['blog_section']['title'] ?? '' ),
		'text'           => tolstenko_kses_html( $raw['blog_section']['text'] ?? '' ),
		'posts_per_page' => isset( $raw['blog_section']['posts_per_page'] ) ? (int) $raw['blog_section']['posts_per_page'] : 12,
		'ids'            => tolstenko_sanitize_service_section_ids( $raw['blog_section']['ids'] ?? array() ),
	);

	$out['blog_section_filters'] = array(
		'title'          => tolstenko_kses_html( $raw['blog_section_filters']['title'] ?? '' ),
		'text'           => tolstenko_kses_html( $raw['blog_section_filters']['text'] ?? '' ),
		'posts_per_page' => isset( $raw['blog_section_filters']['posts_per_page'] ) ? (int) $raw['blog_section_filters']['posts_per_page'] : 4,
		'ids'            => tolstenko_sanitize_service_section_ids( $raw['blog_section_filters']['ids'] ?? array() ),
		'btn_text'       => sanitize_text_field( $raw['blog_section_filters']['btn_text'] ?? '' ),
	);

	$out['blog_section_tile'] = array(
		'title'           => tolstenko_kses_html( $raw['blog_section_tile']['title'] ?? '' ),
		'text'            => tolstenko_kses_html( $raw['blog_section_tile']['text'] ?? '' ),
		'posts_per_page'  => isset( $raw['blog_section_tile']['posts_per_page'] ) ? (int) $raw['blog_section_tile']['posts_per_page'] : 9,
		'ids'             => tolstenko_sanitize_service_section_ids( $raw['blog_section_tile']['ids'] ?? array() ),
		'sidebar_name'    => sanitize_text_field( $raw['blog_section_tile']['sidebar_name'] ?? '' ),
		'sidebar_text'    => sanitize_textarea_field( $raw['blog_section_tile']['sidebar_text'] ?? '' ),
		'sidebar_btn'     => sanitize_text_field( $raw['blog_section_tile']['sidebar_btn'] ?? '' ),
		'sidebar_btn_url' => esc_url_raw( $raw['blog_section_tile']['sidebar_btn_url'] ?? '' ),
		'sidebar_photo'   => isset( $raw['blog_section_tile']['sidebar_photo'] ) ? (int) $raw['blog_section_tile']['sidebar_photo'] : 0,
	);

	$out['consultation_whatsapp'] = array(
		'title'       => tolstenko_kses_html( $raw['consultation_whatsapp']['title'] ?? ''  ),
		'text'        => tolstenko_kses_html( $raw['consultation_whatsapp']['text'] ?? ''  ),
		'btn_text'    => sanitize_text_field( $raw['consultation_whatsapp']['btn_text'] ?? '' ),
		'btn_url'     => esc_url_raw( $raw['consultation_whatsapp']['btn_url'] ?? '' ),
		'color'       => sanitize_hex_color( $raw['consultation_whatsapp']['color'] ?? '' ) ?: sanitize_text_field( $raw['consultation_whatsapp']['color'] ?? '' ),
		'color_hover' => sanitize_hex_color( $raw['consultation_whatsapp']['color_hover'] ?? '' ) ?: sanitize_text_field( $raw['consultation_whatsapp']['color_hover'] ?? '' ),
	);

	$out['consultation_tg'] = array(
		'title'    => tolstenko_kses_html( $raw['consultation_tg']['title'] ?? ''  ),
		'text'     => tolstenko_kses_html( $raw['consultation_tg']['text'] ?? ''  ),
		'btn_text' => sanitize_text_field( $raw['consultation_tg']['btn_text'] ?? '' ),
		'btn_url'  => esc_url_raw( $raw['consultation_tg']['btn_url'] ?? '' ),
		'text_btn' => tolstenko_kses_html( $raw['consultation_tg']['text_btn'] ?? ''  ),
		'image'    => isset( $raw['consultation_tg']['image'] ) ? (int) $raw['consultation_tg']['image'] : 0,
	);

	$out['consultation_tel'] = array(
		'title'              => tolstenko_kses_html( $raw['consultation_tel']['title'] ?? '' ),
		'message'            => tolstenko_kses_html( $raw['consultation_tel']['message'] ?? '' ),
		'position'           => sanitize_text_field( $raw['consultation_tel']['position'] ?? '' ),
		'phone'              => sanitize_text_field( $raw['consultation_tel']['phone'] ?? '' ),
		'btn_tel_text'       => sanitize_text_field( $raw['consultation_tel']['btn_tel_text'] ?? '' ),
		'btn_messenger_text' => sanitize_text_field( $raw['consultation_tel']['btn_messenger_text'] ?? '' ),
		'btn_messenger_url'  => esc_url_raw( $raw['consultation_tel']['btn_messenger_url'] ?? '' ),
		'color'              => sanitize_hex_color( $raw['consultation_tel']['color'] ?? '' ) ?: sanitize_text_field( $raw['consultation_tel']['color'] ?? '' ),
		'color_hover'        => sanitize_hex_color( $raw['consultation_tel']['color_hover'] ?? '' ) ?: sanitize_text_field( $raw['consultation_tel']['color_hover'] ?? '' ),
		'image'              => isset( $raw['consultation_tel']['image'] ) ? (int) $raw['consultation_tel']['image'] : 0,
	);

	$out['consultation_free'] = array(
		'title'          => tolstenko_kses_html( $raw['consultation_free']['title'] ?? ''  ),
		'text'           => tolstenko_kses_html( $raw['consultation_free']['text'] ?? ''  ),
		'subtitle'       => tolstenko_kses_html( $raw['consultation_free']['subtitle'] ?? ''  ),
		'contacts_label' => tolstenko_kses_html( $raw['consultation_free']['contacts_label'] ?? ''  ),
		'phone'          => sanitize_text_field( $raw['consultation_free']['phone'] ?? '' ),
		'telegram_url'   => esc_url_raw( $raw['consultation_free']['telegram_url'] ?? '' ),
		'whatsapp_url'   => esc_url_raw( $raw['consultation_free']['whatsapp_url'] ?? '' ),
		'vk_url'         => esc_url_raw( $raw['consultation_free']['vk_url'] ?? '' ),
		'image'          => isset( $raw['consultation_free']['image'] ) ? (int) $raw['consultation_free']['image'] : 0,
	);

	$out['free_audit'] = array(
		'btn_text' => sanitize_text_field( $raw['free_audit']['btn_text'] ?? '' ),
		'btn_url'  => esc_url_raw( $raw['free_audit']['btn_url'] ?? '' ),
		'items'    => array(),
	);
	if ( isset( $raw['free_audit']['items'] ) && is_array( $raw['free_audit']['items'] ) ) {
		foreach ( $raw['free_audit']['items'] as $it ) {
			$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $it !== '' ) {
				$out['free_audit']['items'][] = sanitize_text_field( $it );
			}
		}
	}

	$out['solution'] = array(
		'title'         => tolstenko_kses_html( $raw['solution']['title'] ?? '' ),
		'text'          => tolstenko_kses_html( $raw['solution']['text'] ?? '' ),
		'items'         => array(),
		'items_second'  => array(),
	);
	if ( isset( $raw['solution']['items'] ) && is_array( $raw['solution']['items'] ) ) {
		foreach ( $raw['solution']['items'] as $it ) {
			$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $it !== '' ) {
				$out['solution']['items'][] = tolstenko_kses_html( $it );
			}
		}
	}
	if ( isset( $raw['solution']['items_second'] ) && is_array( $raw['solution']['items_second'] ) ) {
		foreach ( $raw['solution']['items_second'] as $it ) {
			$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $it !== '' ) {
				$out['solution']['items_second'][] = tolstenko_kses_html( $it );
			}
		}
	}

	$out['one_team'] = array(
		'title'    => tolstenko_kses_html( $raw['one_team']['title'] ?? '' ),
		'btn_text' => tolstenko_kses_html( $raw['one_team']['btn_text'] ?? '' ),
		'btn_url'  => esc_url_raw( $raw['one_team']['btn_url'] ?? '' ),
		'items'    => array(),
	);
	if ( isset( $raw['one_team']['items'] ) && is_array( $raw['one_team']['items'] ) ) {
		foreach ( $raw['one_team']['items'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$value = tolstenko_kses_html( $it['value'] ?? '' );
			$text  = tolstenko_kses_html( $it['text'] ?? '' );
			if ( trim( wp_strip_all_tags( $value ) ) === '' && trim( wp_strip_all_tags( $text ) ) === '' ) {
				continue;
			}
			$out['one_team']['items'][] = array(
				'value' => $value,
				'text'  => $text,
			);
		}
	}

	$out['author'] = array(
		'name'            => tolstenko_kses_html( $raw['author']['name'] ?? '' ),
		'photo'           => isset( $raw['author']['photo'] ) ? (int) $raw['author']['photo'] : 0,
		'btn_text'        => sanitize_text_field( $raw['author']['btn_text'] ?? '' ),
		'btn_url'         => esc_url_raw( $raw['author']['btn_url'] ?? '' ),
		'list'            => array(),
		'items'           => array(),
		'links_label'     => sanitize_text_field( $raw['author']['links_label'] ?? '' ),
		'links'           => array(),
		'show_bottom'     => ! empty( $raw['author']['show_bottom'] ),
		'subtitle'        => tolstenko_kses_html( $raw['author']['subtitle'] ?? '' ),
		'text'            => tolstenko_kses_html( $raw['author']['text'] ?? '' ),
		'sublist'         => array(),
		'btn_more_text'   => sanitize_text_field( $raw['author']['btn_more_text'] ?? '' ),
		'btn_more_url'    => esc_url_raw( $raw['author']['btn_more_url'] ?? '' ),
		'award'           => tolstenko_kses_html( $raw['author']['award'] ?? '' ),
		'award_image'     => isset( $raw['author']['award_image'] ) ? (int) $raw['author']['award_image'] : 0,
		'right_image'     => isset( $raw['author']['right_image'] ) ? (int) $raw['author']['right_image'] : 0,
		'speeches'        => array(),
		'btn_invite_text' => sanitize_text_field( $raw['author']['btn_invite_text'] ?? '' ),
		'btn_invite_url'  => esc_url_raw( $raw['author']['btn_invite_url'] ?? '' ),
	);
	if ( isset( $raw['author']['list'] ) && is_array( $raw['author']['list'] ) ) {
		foreach ( $raw['author']['list'] as $it ) {
			$text = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $text !== '' ) {
				$out['author']['list'][] = array( 'text' => tolstenko_kses_html( $text ) );
			}
		}
	}
	if ( isset( $raw['author']['items'] ) && is_array( $raw['author']['items'] ) ) {
		foreach ( $raw['author']['items'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$value = tolstenko_kses_html( $it['value'] ?? '' );
			$text  = tolstenko_kses_html( $it['text'] ?? '' );
			if ( trim( wp_strip_all_tags( $value ) ) === '' && trim( wp_strip_all_tags( $text ) ) === '' ) {
				continue;
			}
			$out['author']['items'][] = array(
				'value' => $value,
				'text'  => $text,
			);
		}
	}
	if ( isset( $raw['author']['links'] ) && is_array( $raw['author']['links'] ) ) {
		foreach ( $raw['author']['links'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$title = sanitize_text_field( $it['title'] ?? '' );
			$url   = esc_url_raw( $it['url'] ?? '' );
			$icon  = isset( $it['icon'] ) ? (int) $it['icon'] : 0;
			if ( $title === '' && $url === '' && $icon <= 0 ) {
				continue;
			}
			$out['author']['links'][] = array(
				'title' => $title,
				'url'   => $url,
				'icon'  => $icon,
			);
		}
	}
	if ( isset( $raw['author']['sublist'] ) && is_array( $raw['author']['sublist'] ) ) {
		foreach ( $raw['author']['sublist'] as $it ) {
			$text = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $text !== '' ) {
				$out['author']['sublist'][] = array( 'text' => tolstenko_kses_html( $text ) );
			}
		}
	}
	if ( isset( $raw['author']['speeches'] ) && is_array( $raw['author']['speeches'] ) ) {
		foreach ( $raw['author']['speeches'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$image = isset( $it['image'] ) ? (int) $it['image'] : 0;
			$text  = tolstenko_kses_html( $it['text'] ?? '' );
			if ( $image <= 0 && trim( wp_strip_all_tags( $text ) ) === '' ) {
				continue;
			}
			$out['author']['speeches'][] = array(
				'image' => $image,
				'text'  => $text,
			);
		}
	}

	$out['different_experiences'] = array(
		'title'      => tolstenko_kses_html( $raw['different_experiences']['title'] ?? ''  ),
		'text'       => tolstenko_kses_html( $raw['different_experiences']['text'] ?? ''  ),
		'tg_text'    => sanitize_text_field( $raw['different_experiences']['tg_text'] ?? '' ),
		'tg_url'     => esc_url_raw( $raw['different_experiences']['tg_url'] ?? '' ),
		'modal_text' => sanitize_text_field( $raw['different_experiences']['modal_text'] ?? '' ),
		'modal_url'  => esc_url_raw( $raw['different_experiences']['modal_url'] ?? '' ),
		'items'      => array(),
	);
	if ( isset( $raw['different_experiences']['items'] ) && is_array( $raw['different_experiences']['items'] ) ) {
		foreach ( $raw['different_experiences']['items'] as $it ) {
			$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $it !== '' ) {
				$out['different_experiences']['items'][] = sanitize_text_field( $it );
			}
		}
	}

	$out['partners'] = array(
		'title' => tolstenko_kses_html( $raw['partners']['title'] ?? ''  ),
		'text'  => tolstenko_kses_html( $raw['partners']['text'] ?? ''  ),
		'items' => array(),
	);
	if ( isset( $raw['partners']['items'] ) && is_array( $raw['partners']['items'] ) ) {
		foreach ( $raw['partners']['items'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$title = tolstenko_kses_html( $it['title'] ?? ''  );
			$image = isset( $it['image'] ) ? (int) $it['image'] : 0;
			if ( $title === '' && $image === 0 ) {
				continue;
			}
			$out['partners']['items'][] = array(
				'title' => $title,
				'image' => $image,
			);
		}
	}

	$out['strategy'] = array(
		'title'          => tolstenko_kses_html( $raw['strategy']['title'] ?? ''  ),
		'subtitle'       => tolstenko_kses_html( $raw['strategy']['subtitle'] ?? ''  ),
		'text'           => tolstenko_kses_html( $raw['strategy']['text'] ?? ''  ),
		'btn_text'       => sanitize_text_field( $raw['strategy']['btn_text'] ?? '' ),
		'btn_url'        => esc_url_raw( $raw['strategy']['btn_url'] ?? '' ),
		'file_text'      => sanitize_text_field( $raw['strategy']['file_text'] ?? '' ),
		'file_url'       => esc_url_raw( $raw['strategy']['file_url'] ?? '' ),
		'contacts_label' => tolstenko_kses_html( $raw['strategy']['contacts_label'] ?? ''  ),
		'phone'          => sanitize_text_field( $raw['strategy']['phone'] ?? '' ),
		'telegram_text'  => sanitize_text_field( $raw['strategy']['telegram_text'] ?? '' ),
		'telegram_url'   => esc_url_raw( $raw['strategy']['telegram_url'] ?? '' ),
		'image'          => isset( $raw['strategy']['image'] ) ? (int) $raw['strategy']['image'] : 0,
		'image_mob'      => isset( $raw['strategy']['image_mob'] ) ? (int) $raw['strategy']['image_mob'] : 0,
		'items'          => array(),
	);
	if ( isset( $raw['strategy']['items'] ) && is_array( $raw['strategy']['items'] ) ) {
		foreach ( $raw['strategy']['items'] as $it ) {
			$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $it !== '' ) {
				$out['strategy']['items'][] = sanitize_text_field( $it );
			}
		}
	}

	$out['team_cards'] = array(
		'title' => tolstenko_kses_html( $raw['team_cards']['title'] ?? ''  ),
		'text'  => tolstenko_kses_html( $raw['team_cards']['text'] ?? ''  ),
		'items' => array(),
	);
	if ( isset( $raw['team_cards']['items'] ) && is_array( $raw['team_cards']['items'] ) ) {
		foreach ( $raw['team_cards']['items'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$name = sanitize_text_field( $it['name'] ?? '' );
			$image = isset( $it['image'] ) ? (int) $it['image'] : 0;
			if ( $name === '' && $image === 0 ) {
				continue;
			}
			$out['team_cards']['items'][] = array(
				'name'     => $name,
				'position' => sanitize_text_field( $it['position'] ?? '' ),
				'exp'      => sanitize_text_field( $it['exp'] ?? '' ),
				'text'     => sanitize_textarea_field( $it['text'] ?? '' ),
				'btn_text' => sanitize_text_field( $it['btn_text'] ?? '' ),
				'btn_url'  => esc_url_raw( $it['btn_url'] ?? '' ),
				'image'    => $image,
			);
		}
	}

	$out['tg_channel'] = array(
		'title'    => tolstenko_kses_html( $raw['tg_channel']['title'] ?? ''  ),
		'text'     => tolstenko_kses_html( $raw['tg_channel']['text'] ?? ''  ),
		'btn_text' => sanitize_text_field( $raw['tg_channel']['btn_text'] ?? '' ),
		'btn_url'  => esc_url_raw( $raw['tg_channel']['btn_url'] ?? '' ),
		'image'    => isset( $raw['tg_channel']['image'] ) ? (int) $raw['tg_channel']['image'] : 0,
		'items'    => array(),
	);
	if ( isset( $raw['tg_channel']['items'] ) && is_array( $raw['tg_channel']['items'] ) ) {
		foreach ( $raw['tg_channel']['items'] as $it ) {
			$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $it !== '' ) {
				$out['tg_channel']['items'][] = sanitize_text_field( $it );
			}
		}
	}

	$out['three_steps'] = array(
		'title' => tolstenko_kses_html( $raw['three_steps']['title'] ?? ''  ),
		'text'  => tolstenko_kses_html( $raw['three_steps']['text'] ?? ''  ),
		'items' => array(),
	);
	if ( isset( $raw['three_steps']['items'] ) && is_array( $raw['three_steps']['items'] ) ) {
		foreach ( $raw['three_steps']['items'] as $it ) {
			$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $it !== '' ) {
				$out['three_steps']['items'][] = sanitize_text_field( $it );
			}
		}
	}

	$out['doubts'] = array(
		'subtitle' => tolstenko_kses_html( $raw['doubts']['subtitle'] ?? '' ),
		'title'    => tolstenko_kses_html( $raw['doubts']['title'] ?? '' ),
		'items'    => array(),
	);
	if ( isset( $raw['doubts']['items'] ) && is_array( $raw['doubts']['items'] ) ) {
		foreach ( $raw['doubts']['items'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$row = array(
				'badge' => sanitize_text_field( $it['badge'] ?? '' ),
				'title' => tolstenko_kses_html( $it['title'] ?? '' ),
				'text'  => tolstenko_kses_html( $it['text'] ?? '' ),
			);
			if ( $row['badge'] === '' && $row['title'] === '' && $row['text'] === '' ) {
				continue;
			}
			$out['doubts']['items'][] = $row;
		}
	}

	$out['familiar'] = array(
		'subtitle' => tolstenko_kses_html( $raw['familiar']['subtitle'] ?? '' ),
		'title'    => tolstenko_kses_html( $raw['familiar']['title'] ?? '' ),
		'text'     => tolstenko_kses_html( $raw['familiar']['text'] ?? '' ),
		'items'    => array(),
	);
	if ( isset( $raw['familiar']['items'] ) && is_array( $raw['familiar']['items'] ) ) {
		foreach ( $raw['familiar']['items'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$row = array(
				'title' => tolstenko_kses_html( $it['title'] ?? '' ),
				'text'  => tolstenko_kses_html( $it['text'] ?? '' ),
			);
			if ( $row['title'] === '' && $row['text'] === '' ) {
				continue;
			}
			$out['familiar']['items'][] = $row;
		}
	}

	$out['result'] = array(
		'subtitle' => tolstenko_kses_html( $raw['result']['subtitle'] ?? '' ),
		'title'    => tolstenko_kses_html( $raw['result']['title'] ?? '' ),
		'items'    => array(),
	);
	if ( isset( $raw['result']['items'] ) && is_array( $raw['result']['items'] ) ) {
		foreach ( $raw['result']['items'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$row = array(
				'ico'   => isset( $it['ico'] ) ? (int) $it['ico'] : 0,
				'title' => tolstenko_kses_html( $it['title'] ?? '' ),
				'text'  => tolstenko_kses_html( $it['text'] ?? '' ),
			);
			if ( ! $row['ico'] && $row['title'] === '' && $row['text'] === '' ) {
				continue;
			}
			$out['result']['items'][] = $row;
		}
	}

	$out['faq'] = array(
		'title'        => tolstenko_kses_html( $raw['faq']['title'] ?? ''  ),
		'text'         => tolstenko_kses_html( $raw['faq']['text'] ?? ''  ),
		'form_title'   => tolstenko_kses_html( $raw['faq']['form_title'] ?? ''  ),
		'form_text'    => tolstenko_kses_html( $raw['faq']['form_text'] ?? ''  ),
		'foto'         => isset( $raw['faq']['foto'] ) ? (int) $raw['faq']['foto'] : 0,
		'foto_text'    => tolstenko_kses_html( $raw['faq']['foto_text'] ?? '' ),
		'phone'        => sanitize_text_field( $raw['faq']['phone'] ?? '' ),
		'telegram_url' => esc_url_raw( $raw['faq']['telegram_url'] ?? '' ),
		'items'        => array(),
	);
	if ( isset( $raw['faq']['items'] ) && is_array( $raw['faq']['items'] ) ) {
		foreach ( $raw['faq']['items'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$q = tolstenko_kses_html( $it['title'] ?? ''  );
			$a = tolstenko_kses_redactor( $it['redactor'] ?? '' );
			if ( $q === '' && trim( wp_strip_all_tags( $a ) ) === '' ) {
				continue;
			}
			$out['faq']['items'][] = array(
				'title'    => $q,
				'redactor' => $a,
			);
		}
	}

	$prev_seo_defaults = get_option( 'tolstenko_block_defaults', array() );
	$prev_seo_blocks   = ( is_array( $prev_seo_defaults ) && isset( $prev_seo_defaults['seo_section']['blocks'] ) && is_array( $prev_seo_defaults['seo_section']['blocks'] ) )
		? $prev_seo_defaults['seo_section']['blocks']
		: array();
	$raw_seo_blocks    = ( isset( $raw['seo_section']['blocks'] ) && is_array( $raw['seo_section']['blocks'] ) )
		? $raw['seo_section']['blocks']
		: $prev_seo_blocks;

	$out['seo_section'] = array(
		'title'     => tolstenko_kses_html( $raw['seo_section']['title'] ?? '' ),
		'subtitle'  => tolstenko_kses_html( $raw['seo_section']['subtitle'] ?? '' ),
		'more_text' => sanitize_text_field( $raw['seo_section']['more_text'] ?? '' ),
		'blocks'    => function_exists( 'tolstenko_sanitize_seo_section_blocks_raw' )
			? tolstenko_sanitize_seo_section_blocks_raw( $raw_seo_blocks )
			: array(),
	);

	// Не затирать дефолты блоков тела статьи (правятся на отдельной странице).
	$prev = get_option( 'tolstenko_block_defaults', array() );
	if ( is_array( $prev ) && function_exists( 'tolstenko_blog_content_defaults_schema' ) ) {
		foreach ( array_keys( tolstenko_blog_content_defaults_schema() ) as $content_key ) {
			if ( isset( $prev[ $content_key ] ) ) {
				$out[ $content_key ] = $prev[ $content_key ];
			}
		}
	}

	// Не затирать дефолты партнёрских / пресс-портретных блоков (отдельные страницы настроек).
	if ( is_array( $prev ) && function_exists( 'tolstenko_partner_press_defaults_keys' ) ) {
		foreach ( tolstenko_partner_press_defaults_keys() as $pp_key ) {
			if ( isset( $prev[ $pp_key ] ) ) {
				$out[ $pp_key ] = $prev[ $pp_key ];
			}
		}
	}

	// Не затирать шаблон вакансии (отдельная страница настроек).
	if ( is_array( $prev ) && function_exists( 'tolstenko_vacancy_template_schema' ) ) {
		foreach ( array_keys( tolstenko_vacancy_template_schema() ) as $vac_key ) {
			if ( isset( $prev[ $vac_key ] ) ) {
				$out[ $vac_key ] = $prev[ $vac_key ];
			}
		}
	}

	// Не затирать видео-пузырь (отдельная страница настроек).
	if ( is_array( $prev ) && isset( $prev['video_bubble'] ) ) {
		$out['video_bubble'] = $prev['video_bubble'];
	}

	update_option( 'tolstenko_block_defaults', $out, false );
}