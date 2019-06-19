-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 17 2019 г., 17:26
-- Версия сервера: 5.7.20
-- Версия PHP: 7.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `new`
--

-- --------------------------------------------------------

--
-- Структура таблицы `background`
--

CREATE TABLE `background` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `background` varchar(255) DEFAULT NULL,
  `about` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `background`
--

INSERT INTO `background` (`id`, `name`, `background`, `about`) VALUES
(1, 'Окраина деревни', 'default.png', NULL),
(2, 'Подвал', 'basement.png', 'Шанс получить за победу над боссом «Жаба»'),
(3, 'Гаражи', 'garages.png', 'Шанс получить за победу над боссом «Клык»'),
(4, 'Заброшка', 'building.png', 'Шанс получить за победу над боссом «Строитель»'),
(5, 'Лаборатория', 'laboratory.png', 'Шанс получить за победу над боссом «Эксперимент #13»');

-- --------------------------------------------------------

--
-- Структура таблицы `background_users`
--

CREATE TABLE `background_users` (
  `id` bigint(20) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_background` int(11) NOT NULL DEFAULT '1',
  `used` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `banned`
--

CREATE TABLE `banned` (
  `id` bigint(20) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_who` int(11) DEFAULT NULL,
  `reason` text,
  `method` enum('0','1') NOT NULL DEFAULT '0',
  `time_ban` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `chat`
--

CREATE TABLE `chat` (
  `id` bigint(20) NOT NULL,
  `id_user` int(11) NOT NULL,
  `message` text NOT NULL,
  `time` int(11) NOT NULL,
  `answer` int(11) NOT NULL,
  `type` set('default','trade','fights','groups') NOT NULL DEFAULT 'default',
  `who` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `dialogs`
--

CREATE TABLE `dialogs` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `last_time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `fights`
--

CREATE TABLE `fights` (
  `id` bigint(20) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_boss` int(11) DEFAULT NULL,
  `hp_boss` int(11) NOT NULL DEFAULT '1000',
  `type_boss` set('solo','party','clan') NOT NULL DEFAULT 'solo',
  `date_fight` int(11) DEFAULT NULL,
  `date_start` int(11) DEFAULT NULL,
  `date_end` int(11) DEFAULT NULL,
  `reward` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `fights_boss`
--

CREATE TABLE `fights_boss` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `background` int(11) DEFAULT NULL,
  `health` bigint(20) NOT NULL DEFAULT '1000',
  `bolts` bigint(20) NOT NULL DEFAULT '0',
  `repute` bigint(20) NOT NULL DEFAULT '0',
  `need_key` int(11) NOT NULL DEFAULT '1',
  `give_key` int(11) NOT NULL DEFAULT '1',
  `min_damage` int(11) NOT NULL DEFAULT '3',
  `max_damage` int(11) NOT NULL DEFAULT '10'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `fights_boss`
--

INSERT INTO `fights_boss` (`id`, `name`, `background`, `health`, `bolts`, `repute`, `need_key`, `give_key`, `min_damage`, `max_damage`) VALUES
(1, 'Виталий «Жаба»', 2, 1000, 50, 30, 0, 2, 3, 10),
(2, 'Дмитрий «Клык»', 3, 5000, 100, 50, 2, 3, 7, 15),
(3, '«Строитель»', 4, 15000, 150, 75, 3, 4, 15, 30),
(4, '«Эксперимент #13»', 5, 35000, 250, 100, 4, 5, 30, 50);

-- --------------------------------------------------------

--
-- Структура таблицы `fights_boss_awards`
--

CREATE TABLE `fights_boss_awards` (
  `id` bigint(20) NOT NULL,
  `id_boss` int(11) NOT NULL,
  `id_weapon` int(11) NOT NULL,
  `percent` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `fights_boss_awards`
--

INSERT INTO `fights_boss_awards` (`id`, `id_boss`, `id_weapon`, `percent`) VALUES
(1, 3, 201, 33),
(2, 3, 200, 33),
(3, 3, 198, 33);

-- --------------------------------------------------------

--
-- Структура таблицы `fights_logs`
--

CREATE TABLE `fights_logs` (
  `id_fight` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `log` text,
  `time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `fights_members`
--

CREATE TABLE `fights_members` (
  `id` bigint(20) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_fight` int(11) NOT NULL,
  `time_add` int(11) DEFAULT NULL,
  `damage` bigint(20) NOT NULL DEFAULT '0',
  `banned` enum('0','1') NOT NULL DEFAULT '0',
  `end` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `fights_users`
--

CREATE TABLE `fights_users` (
  `id` bigint(20) NOT NULL,
  `id_user` bigint(20) NOT NULL,
  `success_1` bigint(20) NOT NULL DEFAULT '0',
  `timeout_1` int(11) DEFAULT NULL,
  `success_2` bigint(20) NOT NULL DEFAULT '0',
  `timeout_2` int(11) DEFAULT NULL,
  `success_3` bigint(20) NOT NULL DEFAULT '0',
  `timeout_3` int(11) DEFAULT NULL,
  `success_4` bigint(20) NOT NULL DEFAULT '0',
  `timeout_4` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `forum`
--

CREATE TABLE `forum` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `about` text,
  `canAdd` set('all','admin') NOT NULL DEFAULT 'all'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `forum`
--

INSERT INTO `forum` (`id`, `name`, `about`, `canAdd`) VALUES
(1, 'Новости', 'Новости и обновления игры', 'all');

-- --------------------------------------------------------

--
-- Структура таблицы `forum_posts`
--

CREATE TABLE `forum_posts` (
  `id` bigint(20) NOT NULL,
  `id_topic` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `message` text NOT NULL,
  `timeAdd` int(11) NOT NULL,
  `timeUpd` int(11) DEFAULT NULL,
  `whoUpd` int(11) DEFAULT NULL,
  `answer` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `forum_posts`
--

INSERT INTO `forum_posts` (`id`, `id_topic`, `id_user`, `message`, `timeAdd`, `timeUpd`, `whoUpd`, `answer`) VALUES
(1, 1, 1, '[quote]Обсуждение закрыто <br/>Причина: данная тема не нуждается в обсуждении.[/quote]', 1524067296, NULL, NULL, NULL),
(2, 2, 1, '[quote]Обсуждение закрыто <br/>Причина: в обсуждении не нуждается[/quote]', 1524876628, NULL, NULL, NULL),
(3, 5, 1, '[quote]Обсуждение начато[/quote]', 1524876854, NULL, NULL, NULL),
(4, 6, 1, '[quote]Обсуждение начато[/quote]', 1524936147, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `forum_topics`
--

CREATE TABLE `forum_topics` (
  `id` bigint(20) NOT NULL,
  `id_forum` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `message` text,
  `timeAdd` int(11) NOT NULL,
  `timeUpd` int(11) DEFAULT NULL,
  `whoUpd` int(11) DEFAULT NULL,
  `closed` enum('0','1') NOT NULL DEFAULT '0',
  `pin` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `forum_topics`
--

INSERT INTO `forum_topics` (`id`, `id_forum`, `id_user`, `name`, `message`, `timeAdd`, `timeUpd`, `whoUpd`, `closed`, `pin`) VALUES
(1, 1, 1, 'Обновление от 18.04.2018', '[b]Добавлено:[/b]\r\n1. [act=ladder]Ладдер[/act].\r\n - Еженедельное соревнование местных сталкеров между собой в рукопашном сражении.\r\n2. [act=forum]Форум[/act].\r\n- Изменение обсуждения.\r\n- Закрытие/открытие обсуждения.\r\n- Закрепление/открепление обсуждения.\r\n- Удаление обсуждения.\r\n3. Пополнение здоровья.\r\n- 20% от максимального в минуту.\r\n4. Пополнение энергии.\r\n- 2 единицы в 5 минут.\r\n[b]Обновлено:[/b]\r\n1. Если пользователь заблокирован, то его сообщения автоматически скрываются от игроков (администрация их видит всегда).\r\n[b]Исправлено:[/b]\r\n1. Игрок больше не сможет изменить текст своего обсуждения, если оно закрыто.\r\n2. Сортировка обсуждений в списке всех обсуждение теперь зависит от (1) закреплена тема или нет, (2) последнего сообщения в обсуждении.', 1524066320, 1524068424, 1, '1', '0'),
(2, 1, 1, 'Обновление от 19 по 28.04.2018', '[b]Добавлено:[/b]\r\n1. [act=groups]Группировки (далее ГП)[/act].\r\n- Список участников ГП.\r\n- Строения ГП (и их прокачка).\r\n- Схрон.\r\n- Лог действий участников для лидера и его зама.\r\n- Вывод информации о ГП в профиле игрока.\r\n- Переработка класса для работы с  ГП.\r\n- Начисление опыта ГП от игроков.\r\n- Инвайт в ГП и его принятие/отклонение.\r\n[b]Обновлено:[/b]\r\n1. Полная переработка вещей и их характеристик.\r\n2. Оптимизация восстановления здоровья и энергии.\r\n3. Вещи теперь могут увеличивать максимальное кол-во здоровья и энергии.\r\n[b]Исправлено:[/b]\r\n1. Блоки в профиле игрока теперь отображаются правильно.\r\n2. Баг с неправильной сортировкой обсуждений на форуме.', 1524872535, 1524876931, 1, '1', '0'),
(6, 1, 1, 'Обновление от 28.04.2018', '[b]Обновлено:[/b]\r\n1. Полная переработка дизайна окрестностей.\r\n2. Шрифт перенесен с google fonts на наш сервер, в связи с блокировкой РКН некоторых IP адрес компании Google.\r\n[b]Удалено:[/b]\r\n1. Артефакты (верну позже в лучшем виде).', 1524936147, 1524936201, 1, '0', '0');

-- --------------------------------------------------------

--
-- Структура таблицы `friends`
--

CREATE TABLE `friends` (
  `id` bigint(20) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_friend` int(11) NOT NULL,
  `request` enum('0','1') NOT NULL DEFAULT '0',
  `time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `groups`
--

CREATE TABLE `groups` (
  `id` bigint(20) NOT NULL,
  `id_lider` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `about` text,
  `exp` bigint(20) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '1',
  `max_users` int(11) NOT NULL DEFAULT '5',
  `dateCreate` int(11) DEFAULT NULL,
  `fire` enum('0','1') NOT NULL DEFAULT '0',
  `barracks` int(11) NOT NULL DEFAULT '0',
  `bolts` bigint(20) NOT NULL DEFAULT '0',
  `rubles` bigint(20) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `groups_logs`
--

CREATE TABLE `groups_logs` (
  `id` bigint(20) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_other` int(11) DEFAULT NULL,
  `id_group` int(11) DEFAULT NULL,
  `text` text,
  `time` int(11) DEFAULT NULL,
  `types` set('stash','user','build') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `groups_users`
--

CREATE TABLE `groups_users` (
  `id` bigint(20) NOT NULL,
  `id_group` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `rank` enum('0','1','2','3','4') NOT NULL DEFAULT '0',
  `exp_today` bigint(20) NOT NULL DEFAULT '0',
  `exp_all` bigint(20) NOT NULL DEFAULT '0',
  `accept` enum('0','1') NOT NULL DEFAULT '0',
  `dateAdd` int(11) DEFAULT NULL,
  `donate_bolts` bigint(20) NOT NULL DEFAULT '0',
  `donate_rubles` bigint(20) NOT NULL DEFAULT '0',
  `invite` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `ladder`
--

CREATE TABLE `ladder` (
  `id` bigint(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `points` int(11) NOT NULL DEFAULT '1000',
  `win` int(11) NOT NULL DEFAULT '0',
  `lose` int(11) NOT NULL DEFAULT '0',
  `addDate` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `ladder_fights`
--

CREATE TABLE `ladder_fights` (
  `id_user` int(11) DEFAULT NULL,
  `id_enemy` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `read` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL,
  `type` set('contact','dialog') NOT NULL DEFAULT 'contact'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `notify`
--

CREATE TABLE `notify` (
  `id_user` int(11) DEFAULT NULL,
  `note` text,
  `time` int(11) DEFAULT NULL,
  `view` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `objects`
--

CREATE TABLE `objects` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `about` text,
  `types` set('none','hp','energy','key') NOT NULL DEFAULT 'none',
  `what` int(11) DEFAULT NULL,
  `price` set('bolts','rubles') DEFAULT NULL,
  `amount` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `objects`
--

INSERT INTO `objects` (`id`, `name`, `about`, `types`, `what`, `price`, `amount`) VALUES
(1, 'Бинт', 'Восстанавливает 5% здоровья.', 'hp', 5, 'bolts', 20),
(2, 'Медаль за «Жаба»', 'Медаль за победу над боссом \"Виталий «Жаба»\"', 'key', NULL, NULL, NULL),
(3, 'Медаль за «Клык»', 'Медаль за победу над боссом \"Дмитрий «Клык»\"', 'key', NULL, NULL, NULL),
(4, 'Медаль за «Строитель»', 'Медаль за победу над боссом \"«Строитель»\"', 'key', NULL, NULL, NULL),
(5, 'Медаль за «Эксперимент #13»', 'Медаль за победу над боссом \"«Эксперимент #13»\"', 'key', NULL, NULL, NULL),
(6, 'Медаль за «[неизвестно]»', 'Медаль за победу над боссом \"[неизвестно]\"', 'key', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `objects_users`
--

CREATE TABLE `objects_users` (
  `id` bigint(20) NOT NULL,
  `id_object` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '1',
  `dateAdd` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `login` varchar(32) NOT NULL,
  `password` varchar(64) NOT NULL,
  `access` enum('0','1','2','3') NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '1',
  `exp` bigint(20) NOT NULL DEFAULT '0',
  `repute` bigint(20) NOT NULL DEFAULT '0',
  `bolts` int(11) NOT NULL DEFAULT '100',
  `rubles` int(11) NOT NULL DEFAULT '50',
  `energy` int(11) NOT NULL DEFAULT '50',
  `max_energy` int(11) NOT NULL DEFAULT '50',
  `hp` int(11) NOT NULL DEFAULT '100',
  `max_hp` int(11) NOT NULL DEFAULT '100',
  `boot` bigint(20) NOT NULL DEFAULT '3',
  `hand` bigint(20) NOT NULL DEFAULT '3',
  `head` bigint(20) NOT NULL DEFAULT '3',
  `knife` bigint(20) NOT NULL DEFAULT '2',
  `pistol` bigint(20) NOT NULL DEFAULT '1',
  `gun` bigint(20) NOT NULL DEFAULT '0',
  `save` enum('0','1') NOT NULL DEFAULT '0',
  `start` enum('0','1','2') NOT NULL DEFAULT '0',
  `addDate` int(11) DEFAULT NULL,
  `updDate` int(11) DEFAULT NULL,
  `hikeTime` int(11) DEFAULT NULL,
  `power` int(11) NOT NULL DEFAULT '10',
  `dash` int(11) NOT NULL DEFAULT '3',
  `defense` int(11) NOT NULL DEFAULT '5'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users_data`
--

CREATE TABLE `users_data` (
  `id_user` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `head` int(11) DEFAULT NULL,
  `eyes` int(11) DEFAULT NULL,
  `color` int(11) DEFAULT NULL,
  `beard` int(11) DEFAULT NULL,
  `hair` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `weapons`
--

CREATE TABLE `weapons` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `about` text,
  `slot` set('boot','hand','body','head','knife','pistol','gun') DEFAULT NULL,
  `quality` set('trash','normal','rare','heroic','souvenir') DEFAULT NULL,
  `how` set('shop','random','craft','none','boss') NOT NULL DEFAULT 'random',
  `price` set('bolts','rubles') DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `lvl` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `weapons`
--

INSERT INTO `weapons` (`id`, `name`, `about`, `slot`, `quality`, `how`, `price`, `amount`, `lvl`) VALUES
(1, 'AC-556', 'AC-556', 'gun', 'trash', 'shop', 'bolts', 50, 1),
(2, 'АК-74', 'Автомат АК - 74 (Автомат Калашникова обр. 1974 года) был создан Михаилом Тимофеевичем Калашниковым в  середине 70х годов и  является индивидуальным оружием предназначенным для вооружения личного состава подразделений армии и сил охраны правопорядка. В 1974 году был принят на вооружение Советской Армии взамен автомата АК - 47. Конструкция автомата серьезных изменений не претерпела.', 'gun', 'normal', 'shop', 'bolts', 75, 1),
(3, 'АКМ', 'АКМ', 'gun', 'trash', 'shop', 'bolts', 60, 1),
(4, 'AKS-74U', 'AKS-74U', 'gun', 'trash', 'shop', 'bolts', 75, 1),
(5, 'AK5', 'AK5', 'gun', 'trash', 'shop', 'bolts', 70, 1),
(6, 'AMD-65', 'AMD-65', 'gun', 'trash', 'random', '', NULL, 1),
(7, 'AMP-69', 'AMP-69', 'gun', 'trash', 'shop', 'bolts', 80, 1),
(8, 'AR-10', 'AR-10', 'gun', 'trash', 'shop', 'bolts', 100, 1),
(9, 'AR-18', 'AR-18', 'gun', 'normal', 'shop', 'bolts', 110, 2),
(10, 'Barrett REC7', 'Barrett REC7', 'gun', 'normal', 'random', 'bolts', 0, 2),
(11, 'Bushmaster ACR', 'Bushmaster ACR', 'gun', 'rare', 'random', 'bolts', 0, 2),
(12, 'Bushmaster M17S', 'Bushmaster M17S', 'gun', 'normal', 'random', 'bolts', 0, 2),
(13, 'Cei-Rigotti', 'Cei-Rigotti', 'gun', 'trash', 'random', 'bolts', 0, 2),
(14, 'FAMAS', 'FAMAS', 'gun', 'normal', 'shop', 'bolts', 125, 2),
(15, 'FG-42', 'FG-42', 'gun', 'normal', 'shop', 'bolts', 125, 2),
(16, 'FN F2000', 'FN F2000', 'gun', 'rare', 'random', 'bolts', 0, 2),
(17, 'FN SCAR', 'FN SCAR', 'gun', 'normal', 'random', 'bolts', 0, 2),
(18, 'HK G36', 'HK G36', 'gun', 'normal', 'random', 'bolts', 0, 2),
(19, 'HK XM8', 'HK XM8', 'gun', 'normal', 'random', '', NULL, 1),
(20, 'Galil', 'Galil', 'gun', 'normal', 'shop', 'bolts', 180, 2),
(21, 'MSBS', 'MSBS', 'gun', 'normal', 'random', 'bolts', 0, 2),
(22, 'M4', 'M4', 'gun', 'rare', 'shop', 'rubles', 5, 2),
(23, 'M16', 'M16', 'gun', 'heroic', 'random', 'bolts', 0, 2),
(24, 'OICW', 'OICW', 'gun', 'normal', 'shop', 'bolts', 200, 2),
(25, 'QBZ-03', 'QBZ-03', 'gun', 'normal', 'shop', 'bolts', 195, 2),
(26, 'QBZ-95', 'QBZ-95', 'gun', 'normal', 'random', 'bolts', 0, 3),
(27, 'Remington GPC', 'Remington GPC', 'gun', 'normal', 'shop', 'bolts', 225, 3),
(28, 'Robinson Armaments XCR', 'Robinson Armaments XCR', 'gun', 'rare', 'random', '', NULL, 1),
(29, 'SA80', 'SA80', 'gun', 'normal', 'random', 'bolts', 0, 3),
(30, 'SAR-21', 'SAR-21', 'gun', 'normal', 'shop', 'bolts', 240, 3),
(31, 'SR-47', 'SR-47', 'gun', 'normal', 'shop', 'bolts', 250, 3),
(32, 'SIG 516', 'SIG 516', 'gun', 'normal', 'random', 'bolts', 0, 3),
(33, 'SIG SG 510', 'SIG SG 510', 'gun', 'normal', 'random', 'bolts', 0, 3),
(34, 'Steyr ACR', 'Steyr ACR', 'gun', 'normal', 'shop', 'bolts', 350, 4),
(35, 'Steyr AUG', 'Steyr AUG', 'gun', 'normal', 'random', 'bolts', 0, 4),
(36, 'Stoner 63', 'Stoner 63', 'gun', 'trash', 'random', 'bolts', 0, 4),
(37, 'StG 44', 'StG 44', 'gun', 'normal', 'random', 'bolts', 0, 5),
(38, 'TAR-21', 'TAR-21', 'gun', 'normal', 'random', 'bolts', 0, 5),
(39, 'VHS-K2', 'VHS-K2', 'gun', 'normal', 'shop', 'bolts', 350, 5),
(40, 'XM29 OICW', 'XM29 OICW', 'gun', 'normal', 'shop', 'bolts', 340, 5),
(41, 'Zastava M21', 'Zastava M21', 'gun', 'normal', 'random', 'bolts', 0, 5),
(42, 'Zastava M70', 'Zastava M70', 'gun', 'normal', 'random', 'bolts', 0, 5),
(43, 'АДС', 'АДС', 'gun', 'normal', 'shop', 'bolts', 500, 6),
(44, 'АЕК-971', 'АЕК-971', 'gun', 'normal', 'random', 'bolts', 0, 6),
(45, 'АН-94', 'АН-94', 'gun', 'normal', 'shop', 'bolts', 700, 6),
(46, 'Автомат Фёдорова', 'Автомат Фёдорова', 'gun', 'normal', 'shop', 'bolts', 850, 7),
(47, 'АК-9', 'АК-9', 'gun', 'normal', 'random', 'bolts', 0, 7),
(48, 'Вал', 'Вал', 'gun', 'rare', 'random', 'bolts', 0, 7),
(49, 'Вепр', 'Вепр', 'gun', 'normal', 'random', 'bolts', 800, 7),
(50, 'Гром-С14', 'Гром-С14', 'gun', 'normal', 'shop', 'bolts', 1000, 8),
(51, 'Accuracy International AS50', 'Accuracy International AS50', 'gun', 'rare', 'random', '', NULL, 1),
(52, 'AWM', 'AWM', 'gun', 'rare', 'random', '', NULL, 1),
(53, 'AMP Technical Services DSR-1', 'AMP Technical Services DSR-1', 'gun', 'rare', 'random', '', NULL, 1),
(54, 'AMR-2', 'AMR-2', 'gun', 'rare', 'random', '', NULL, 1),
(55, 'Barrett M82', 'Barrett M82', 'gun', 'heroic', 'random', '', NULL, 1),
(56, 'Barrett MRAD', 'Barrett MRAD', 'gun', 'rare', 'random', '', NULL, 1),
(57, 'ВСК-94', 'ВСК-94', 'gun', 'rare', 'random', '', NULL, 1),
(58, 'ВСС Винторез', 'ВСС Винторез', 'gun', 'rare', 'random', '', NULL, 1),
(59, 'СВ-98', 'СВ-98', 'gun', 'rare', 'random', '', NULL, 1),
(60, 'СВД', 'СВД', 'gun', 'rare', 'random', '', NULL, 1),
(61, 'Повязка на глаз', 'Черная повязка на глаз', 'head', 'trash', 'random', '', NULL, 1),
(62, 'Очки', 'Простые очки в прямоугольной оправе', 'head', 'trash', 'random', '', NULL, 1),
(63, 'Солнцезащитные очки', '', 'head', 'trash', 'random', '', NULL, 1),
(64, 'Бандана', '', 'head', 'trash', 'random', '', NULL, 1),
(65, 'Маска сварщика', 'Маска сварщика', 'head', 'rare', 'random', '', NULL, 1),
(66, 'Маска панорамная МАГ-2', 'Маска панорамная МАГ-2', 'head', 'normal', 'random', '', NULL, 1),
(67, 'Баллистическая маска', 'Баллистическая маска', 'head', 'normal', 'random', '', NULL, 1),
(68, 'Маска Джейсона', 'Маска Джейсона', 'head', 'normal', 'random', '', NULL, 1),
(69, 'Маска - бандана', 'Маска - бандана', 'head', 'normal', 'random', '', NULL, 1),
(70, 'Маска «Zero»', 'Маска «Zero»', 'head', 'rare', 'random', '', NULL, 1),
(71, 'Респиратор', 'Респиратор', 'head', 'normal', 'random', '', NULL, 1),
(72, 'Малый респиратор', 'Малый респиратор', 'head', 'normal', 'random', '', NULL, 1),
(73, 'Медицинская маска', 'Медицинская маска', 'head', 'trash', 'random', '', NULL, 1),
(74, 'Очки для сноуборда', 'Очки для сноуборда', 'head', 'trash', 'random', '', NULL, 1),
(75, 'Балаклава «Reptile»', 'Балаклава «Reptile»', 'head', 'heroic', 'random', '', NULL, 1),
(76, 'Балаклава', 'Балаклава', 'head', 'normal', 'random', '', NULL, 1),
(77, 'Каска строительная', 'Каска строительная', 'head', 'normal', 'random', '', NULL, 1),
(78, 'Шлем-каска ШКПС', 'Шлем-каска ШКПС', 'head', 'normal', 'random', '', NULL, 1),
(79, 'Мотошлем «Хищник»', 'Мотошлем «Хищник»', 'head', 'rare', 'random', '', NULL, 1),
(80, 'Каска солдатская', 'Каска солдатская', 'head', 'normal', 'random', '', NULL, 1),
(81, 'Каска спасателя', 'Каска спасателя', 'head', 'normal', 'random', '', NULL, 1),
(82, 'Ведро', 'Ведро', 'head', 'trash', 'random', '', NULL, 1),
(83, 'Мотошлем «Star»', 'Мотошлем «Star»', 'head', 'normal', 'random', '', NULL, 1),
(84, 'Мотошлем «Чужой»', 'Мотошлем «Чужой»', 'head', 'rare', 'random', '', NULL, 1),
(85, 'Боксерский шлем', 'Боксерский шлем', 'head', 'trash', 'random', '', NULL, 1),
(86, 'Мотошлем «Авиатор»', 'Мотошлем «Авиатор»', 'head', 'normal', 'random', '', NULL, 1),
(87, 'Мотошлем «Predator»', 'Мотошлем «Predator»', 'head', 'rare', 'random', '', NULL, 1),
(88, 'Шляпа шерифа', 'Шляпа шерифа', 'head', 'trash', 'random', '', NULL, 1),
(89, 'Фермерская шляпа', 'Фермерская шляпа', 'head', 'trash', 'random', '', NULL, 1),
(90, 'Кепка', 'Кепка', 'head', 'normal', 'random', '', NULL, 1),
(91, 'Кепка ушанка', '', 'head', 'normal', 'random', '', NULL, 1),
(92, 'Фуражка общевойсковая', 'Фуражка общевойсковая', 'head', 'normal', 'random', '', NULL, 1),
(93, 'Фуражка ФСБ', 'Фуражка ФСБ', 'head', 'normal', 'random', '', NULL, 1),
(94, 'Фуражка «Марио»', 'Фуражка «Марио»', 'head', 'souvenir', 'none', '', NULL, 1),
(95, 'Берет ВДВ', 'Берет ВДВ', 'head', 'normal', 'random', '', NULL, 1),
(96, 'Берет', 'Берет', 'head', 'normal', 'random', '', NULL, 1),
(97, 'Пилотка', 'Пилотка', 'head', 'normal', 'random', '', NULL, 1),
(98, 'Противогаз ГП-7Б', 'Противогаз ГП-7Б', 'head', 'normal', 'random', '', NULL, 1),
(99, 'Противогаз (полумаска)', 'Противогаз (полумаска)', 'head', 'normal', 'random', '', NULL, 1),
(100, 'Шлем «Леном»-3', '«Леном»-3 — шлем иностранного производства. Состоит на вооружении Миротворцев.', 'head', 'rare', 'random', '', NULL, 1),
(101, 'Шлем «Пиранья»', 'Шлем «Пиранья»', 'head', 'normal', 'random', '', NULL, 1),
(102, 'Лётный шлем', 'Лётный шлем', 'head', 'normal', 'random', '', NULL, 1),
(103, 'Маска для страйкбола', 'Маска для страйкбола', 'head', 'heroic', 'random', '', NULL, 1),
(104, 'Средневековый шлем', 'Средневековый шлем', 'head', 'rare', 'random', '', NULL, 1),
(105, 'Жёлтый лётный шлем', 'Жёлтый лётный шлем', 'head', 'normal', 'random', '', NULL, 1),
(106, 'Шлем «Dragon»', 'Шлем «Dragon»', 'head', 'normal', 'random', '', NULL, 1),
(107, 'Шлем \"М9\" с ПНВ', 'Шлем \"М9\" с ПНВ', 'head', 'normal', 'random', '', NULL, 1),
(108, 'Металлический шлем', 'Металлический шлем', 'head', 'normal', 'random', '', NULL, 1),
(109, 'Самодельный противогаз', 'Самодельный противогаз', 'head', 'normal', 'random', '', NULL, 1),
(110, 'Наушники', 'Наушники', 'head', 'normal', 'random', '', NULL, 1),
(111, 'Рабочие перчатки', 'Рабочие перчатки', 'hand', 'trash', 'random', '', NULL, 1),
(112, 'Перчатки латексные', 'Перчатки латексные', 'hand', 'trash', 'random', '', NULL, 1),
(113, 'Перчатки беспалые', 'Перчатки беспалые', 'hand', 'normal', 'random', '', NULL, 1),
(114, 'Перчатки «Desert»', 'Перчатки «Desert»', 'hand', 'normal', 'random', '', NULL, 1),
(115, 'Варежки', 'Варежки', 'hand', 'normal', 'random', '', NULL, 1),
(116, 'Молоток', 'Молоток', 'hand', 'normal', 'random', '', NULL, 1),
(117, 'Бита с проволкой', 'Бита с проволкой', 'hand', 'normal', 'random', '', NULL, 1),
(118, 'Бейсбольная бита', 'Бейсбольная бита', 'hand', 'normal', 'random', '', NULL, 1),
(119, 'Лом-гвоздодер', 'Лом-гвоздодер', 'hand', 'normal', 'random', '', NULL, 1),
(120, 'Гаечный ключ', 'Гаечный ключ', 'hand', 'normal', 'random', '', NULL, 1),
(121, 'Разводной ключ', 'Разводной ключ', 'hand', 'rare', 'random', '', NULL, 1),
(122, 'КПК', 'КПК', 'hand', 'rare', 'random', '', NULL, 1),
(123, 'Бутылка', 'Бутылка', 'hand', 'trash', 'random', '', NULL, 1),
(124, 'Нунчаки', 'Нунчаки', 'hand', 'normal', 'random', '', NULL, 1),
(125, 'Кирпич', 'Кирпич', 'hand', 'trash', 'random', '', NULL, 1),
(126, 'Часы наручные', 'Часы наручные', 'hand', 'normal', 'random', '', NULL, 1),
(127, 'Компас', 'Компас', 'hand', 'normal', 'random', '', NULL, 1),
(128, 'Кастет', 'Кастет', 'hand', 'rare', 'random', '', NULL, 1),
(129, 'Индустриальный кастет', 'Индустриальный кастет', 'hand', 'heroic', 'random', '', NULL, 1),
(130, 'Сковорода', 'Сковорода', 'hand', 'normal', 'random', '', NULL, 1),
(131, 'Клюшка', 'Клюшка', 'hand', 'normal', 'random', '', NULL, 1),
(132, 'Фонарь', 'Фонарь', 'hand', 'normal', 'random', '', NULL, 1),
(133, 'Камень', 'Камень', 'hand', 'trash', 'random', '', NULL, 1),
(134, 'Свинцовая труба', 'Свинцовая труба', 'hand', 'normal', 'random', '', NULL, 1),
(135, 'Перчатка учёного', 'Перчатка учёного', 'hand', 'normal', 'random', '', NULL, 1),
(136, 'Простая дубина', 'Простая дубина', 'hand', 'normal', 'random', '', NULL, 1),
(137, 'Мотоперчатки «Pink»', 'Мотоперчатки «Pink»', 'hand', 'heroic', 'random', '', NULL, 1),
(138, 'Обычная футболка', 'Обычная футболка', 'body', 'trash', 'random', '', NULL, 1),
(139, 'Синяя рубашка', 'Синяя рубашка', 'body', 'trash', 'random', '', NULL, 1),
(140, 'Желтое поло', 'Желтое поло', 'body', 'trash', 'random', '', NULL, 1),
(141, 'Старая майка', 'Старая майка', 'body', 'trash', 'random', '', NULL, 1),
(142, 'Тельняшка', 'Тельняшка', 'body', 'normal', 'random', '', NULL, 1),
(143, 'Черная куртка', 'Черная куртка', 'body', 'normal', 'random', '', NULL, 1),
(144, 'Сигнальная куртка', 'Сигнальная куртка', 'body', 'normal', 'random', '', NULL, 1),
(145, 'Красный жилет', 'Красный жилет', 'body', 'normal', 'random', '', NULL, 1),
(146, 'Страховочный жилет', 'Страховочный жилет', 'body', 'normal', 'random', '', NULL, 1),
(147, 'Спасательный жилет', 'Спасательный жилет', 'body', 'normal', 'random', '', NULL, 1),
(148, 'Разгрузочный жилет', 'Разгрузочный жилет', 'body', 'normal', 'random', '', NULL, 1),
(149, 'Тренерский жилет', 'Тренерский жилет', 'body', 'normal', 'random', '', NULL, 1),
(150, 'Куртка зимняя МО удлиненная', 'Куртка зимняя МО удлиненная', 'body', 'normal', 'random', '', NULL, 1),
(151, 'Армейский Бушлат', 'Армейский Бушлат', 'body', 'normal', 'random', '', NULL, 1),
(152, 'Куртка «Турист»', 'Куртка «Турист»', 'body', 'normal', 'random', '', NULL, 1),
(153, 'Броня «Шакал»', 'Броня «Шакал»', 'body', 'rare', 'random', '', NULL, 1),
(154, 'Полицейский жилет', 'Полицейский жилет', 'body', 'rare', 'random', '', NULL, 1),
(155, 'Бронежилет «Ястреб»', 'Бронежилет «Ястреб»', 'body', 'normal', 'random', '', NULL, 1),
(156, 'Баллистический бронежилет', 'Баллистический бронежилет', 'body', 'normal', 'random', '', NULL, 1),
(157, 'Бронежилет «Шпинат»', 'Бронежилет «Шпинат»', 'body', 'normal', 'random', '', NULL, 1),
(158, 'Бронежилет «Флора»', 'Бронежилет «Флора»', 'body', 'normal', 'random', '', NULL, 1),
(159, 'Бронежилет «Руслан-КВ»', 'Бронежилет «Руслан-КВ»', 'body', 'normal', 'random', '', NULL, 1),
(160, 'Бронежилет «ТАШ»', 'Бронежилет «ТАШ»', 'body', 'normal', 'random', '', NULL, 1),
(161, 'Бронежилет «Ниндзя»', 'Бронежилет «Ниндзя»', 'body', 'normal', 'random', '', NULL, 1),
(162, 'Бронежилет «Сухарь»', 'Бронежилет «Сухарь»', 'body', 'trash', 'random', '', NULL, 1),
(163, 'Кожаный плащ', 'Кожаный плащ', 'body', 'rare', 'random', '', NULL, 1),
(164, 'Мотоциклетная куртка', 'Мотоциклетная куртка', 'body', 'rare', 'random', '', NULL, 1),
(165, 'Мотоциклетная черепаха', 'Мотоциклетная черепаха', 'body', 'heroic', 'random', '', NULL, 1),
(166, 'Заточка', '', 'knife', 'trash', 'random', '', NULL, 1),
(167, 'Опасная бритва', '', 'knife', 'trash', 'random', '', NULL, 1),
(168, 'Вилка', '', 'knife', 'trash', 'random', '', NULL, 1),
(169, 'Топор', '', 'knife', 'trash', 'random', '', NULL, 1),
(170, 'Пожарный топор', '', 'knife', 'normal', 'random', '', NULL, 1),
(171, 'Кирка', '', 'knife', 'normal', 'random', '', NULL, 1),
(172, '«Розочка»', '', 'knife', 'normal', 'random', '', NULL, 1),
(173, 'Лопата складная армейская', '', 'knife', 'rare', 'random', '', NULL, 1),
(174, 'Складной нож', '', 'knife', 'normal', 'random', '', NULL, 1),
(175, 'Нож кухонный', '', 'knife', 'normal', 'random', '', NULL, 1),
(176, 'Самодельный нож', '', 'knife', 'trash', 'random', '', NULL, 1),
(177, 'Мясницкий нож', '', 'knife', 'normal', 'random', '', NULL, 1),
(178, 'Строительный нож', '', 'knife', 'trash', 'random', '', NULL, 1),
(179, 'Шило', '', 'knife', 'normal', 'random', '', NULL, 1),
(180, 'Сюрикен', '', 'knife', 'normal', 'random', '', NULL, 1),
(181, '«Неон»', '', 'knife', 'heroic', 'random', '', NULL, 1),
(182, 'Нож-бабочка', '', 'knife', 'normal', 'random', '', NULL, 1),
(183, 'Баллистический нож', '', 'knife', 'normal', 'random', '', NULL, 1),
(184, 'Штык-нож', '', 'knife', 'normal', 'random', '', NULL, 1),
(185, 'Выкидной нож', '', 'knife', 'normal', 'random', '', NULL, 1),
(186, 'Метательный нож', '', 'knife', 'normal', 'random', '', NULL, 1),
(187, 'Нож Боуи', '', 'knife', 'normal', 'random', '', NULL, 1),
(188, 'Тычковый нож', '', 'knife', 'normal', 'random', '', NULL, 1),
(189, 'Охотничий нож', '', 'knife', 'normal', 'random', '', NULL, 1),
(190, 'Штык-нож M9', '', 'knife', 'normal', 'random', '', NULL, 1),
(191, 'Нож с лезвием-крюком', '', 'knife', 'normal', 'random', '', NULL, 1),
(192, 'Керамбит', '', 'knife', 'normal', 'random', '', NULL, 1),
(193, 'Мачете', '', 'knife', 'normal', 'random', '', NULL, 1),
(194, 'Катана', '', 'knife', 'rare', 'random', '', NULL, 1),
(195, 'Саперная лопатка', '', 'knife', 'rare', 'random', '', NULL, 1),
(196, 'Тесак', '', 'knife', 'normal', 'random', '', NULL, 1),
(197, 'Ледоруб', '', 'knife', 'rare', 'random', '', NULL, 1),
(198, 'Шлем строителя', 'Трофейный шлем за победу на боссом «Строитель»', 'head', 'rare', 'boss', '', NULL, 1),
(199, 'Серп', '', 'knife', 'normal', 'random', '', NULL, 1),
(200, 'Строительная куртка', '', 'body', 'rare', 'boss', '', NULL, 1),
(201, 'Болгарка', '', 'knife', 'rare', 'boss', '', NULL, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `weapons_stats`
--

CREATE TABLE `weapons_stats` (
  `id` bigint(20) NOT NULL,
  `id_weapon` int(11) DEFAULT NULL,
  `atrb` set('boot','hand','head','knife','pistol','gun','power','dash','defense','hp','energy') DEFAULT NULL,
  `bonus` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `weapons_stats`
--

INSERT INTO `weapons_stats` (`id`, `id_weapon`, `atrb`, `bonus`) VALUES
(1, 1, 'gun', 1),
(2, 1, 'power', 1),
(3, 2, 'gun', 1),
(4, 2, 'energy', 2),
(5, 3, 'gun', 2),
(7, 4, 'gun', 1),
(8, 4, 'hp', 5),
(9, 5, 'power', 3),
(10, 6, 'defense', 1),
(11, 6, 'power', 1),
(12, 7, 'power', 2),
(13, 7, 'dash', 1),
(14, 8, 'hp', 5),
(15, 8, 'energy', 2),
(16, 9, 'gun', 2),
(17, 9, 'power', 2),
(18, 11, 'gun', 5),
(19, 11, 'energy', 2),
(20, 11, 'hp', 10),
(21, 11, 'dash', 1),
(22, 10, 'gun', 3),
(23, 10, 'hand', 1),
(24, 12, 'gun', 3),
(25, 12, 'defense', 1),
(26, 13, 'gun', 1),
(27, 13, 'hp', 2),
(28, 13, 'defense', 1),
(29, 14, 'gun', 2),
(30, 14, 'defense', 1),
(31, 14, 'hand', 1),
(32, 15, 'gun', 1),
(33, 15, 'energy', 2),
(34, 15, 'defense', 1),
(35, 16, 'gun', 7),
(36, 16, 'hand', 2),
(37, 16, 'energy', 3),
(38, 16, 'power', 1),
(39, 17, 'hand', 3),
(40, 17, 'power', 1),
(41, 18, 'power', 2),
(42, 18, 'energy', 4),
(43, 19, 'gun', 5),
(44, 20, 'gun', 3),
(45, 20, 'dash', 1),
(46, 20, 'defense', 1),
(47, 21, 'gun', 1),
(48, 21, 'defense', 3),
(49, 22, 'gun', 10),
(50, 22, 'hand', 3),
(51, 22, 'dash', 2),
(52, 22, 'hp', 5),
(53, 23, 'gun', 10),
(54, 23, 'hp', 10),
(55, 23, 'energy', 5),
(56, 23, 'hand', 4),
(57, 23, 'dash', 1),
(58, 23, 'power', 2),
(59, 24, 'gun', 4),
(60, 24, 'hp', 5),
(61, 25, 'hp', 10),
(62, 25, 'energy', 6),
(63, 26, 'gun', 5),
(64, 26, 'hp', 5),
(65, 27, 'gun', 6),
(66, 27, 'dash', 1),
(67, 27, 'power', 1),
(68, 28, 'gun', 7),
(69, 28, 'hand', 3),
(70, 28, 'dash', 1),
(71, 29, 'gun', 2),
(72, 29, 'hand', 5),
(73, 30, 'power', 2),
(74, 30, 'dash', 2),
(75, 30, 'defense', 3),
(76, 31, 'gun', 5),
(77, 31, 'energy', 4),
(78, 31, 'hand', 1),
(79, 32, 'gun', 3),
(80, 32, 'hand', 4),
(81, 33, 'gun', 8),
(82, 34, 'gun', 6),
(83, 34, 'dash', 1),
(84, 34, 'power', 2),
(85, 34, 'hp', 2),
(86, 35, 'gun', 9),
(87, 35, 'hand', 6),
(88, 36, 'dash', 2),
(89, 36, 'energy', 2),
(90, 37, 'pistol', 15),
(91, 37, 'boot', 4),
(92, 37, 'dash', 1),
(93, 38, 'gun', 12),
(94, 38, 'head', 3),
(95, 38, 'power', 2),
(96, 39, 'gun', 20),
(97, 39, 'dash', 2),
(98, 40, 'gun', 13),
(99, 40, 'head', 5),
(100, 41, 'gun', 21),
(101, 41, 'head', 3),
(102, 42, 'gun', 18),
(103, 42, 'power', 4),
(104, 42, 'hp', 5),
(105, 43, 'hp', 20),
(106, 43, 'gun', 10),
(107, 43, 'power', 5),
(108, 44, 'gun', 15),
(109, 44, 'head', 7),
(110, 45, 'gun', 25),
(111, 45, 'defense', 7),
(112, 46, 'gun', 20),
(113, 46, 'hand', 10),
(114, 47, 'gun', 21),
(115, 47, 'head', 3),
(116, 47, 'hand', 5),
(117, 48, 'gun', 30),
(118, 48, 'head', 5),
(119, 48, 'boot', 10),
(120, 48, 'hand', 4),
(121, 49, 'gun', 15),
(122, 49, 'hand', 5),
(123, 49, 'boot', 7),
(124, 50, 'gun', 30),
(125, 50, 'power', 5),
(126, 50, 'hand', 5);

-- --------------------------------------------------------

--
-- Структура таблицы `weapons_users`
--

CREATE TABLE `weapons_users` (
  `id` bigint(20) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_weapon` int(11) NOT NULL,
  `dateAdd` int(11) DEFAULT NULL,
  `used` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `zone`
--

CREATE TABLE `zone` (
  `id` bigint(20) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `quest_1_1` enum('0','1','2','3','4') NOT NULL DEFAULT '0',
  `quest_1_2` enum('0','1','2','3') NOT NULL DEFAULT '0',
  `quest_1_3` enum('0','1','2','3','4','5') NOT NULL DEFAULT '0',
  `quest_1_4` enum('0','1','2','3','4','5') NOT NULL DEFAULT '0',
  `quest_1_5` enum('0','1','2','3','4') NOT NULL DEFAULT '0',
  `quest_1_6` enum('0','1','2','3','4','5','6') NOT NULL DEFAULT '0',
  `quest_1_7` enum('0','1','2','3','4','5','6','7') NOT NULL DEFAULT '0',
  `repute_1` bigint(20) NOT NULL DEFAULT '0',
  `success_1` int(11) NOT NULL DEFAULT '0',
  `quest_2_1` enum('0','1','2','3','4') NOT NULL DEFAULT '0',
  `quest_2_2` enum('0','1','2','3') NOT NULL DEFAULT '0',
  `quest_2_3` enum('0','1','2','3','4','5') NOT NULL DEFAULT '0',
  `quest_2_4` enum('0','1','2','3','4','5') NOT NULL DEFAULT '0',
  `quest_2_5` enum('0','1','2','3','4') NOT NULL DEFAULT '0',
  `quest_2_6` enum('0','1','2','3','4','5','6') NOT NULL DEFAULT '0',
  `quest_2_7` enum('0','1','2','3','4','5','6','7') NOT NULL DEFAULT '0',
  `repute_2` bigint(20) NOT NULL DEFAULT '0',
  `success_2` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `background`
--
ALTER TABLE `background`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `background_users`
--
ALTER TABLE `background_users`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `banned`
--
ALTER TABLE `banned`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `dialogs`
--
ALTER TABLE `dialogs`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `fights`
--
ALTER TABLE `fights`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `fights_boss`
--
ALTER TABLE `fights_boss`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `fights_boss_awards`
--
ALTER TABLE `fights_boss_awards`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `fights_members`
--
ALTER TABLE `fights_members`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `fights_users`
--
ALTER TABLE `fights_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Индексы таблицы `forum`
--
ALTER TABLE `forum`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `groups_logs`
--
ALTER TABLE `groups_logs`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `groups_users`
--
ALTER TABLE `groups_users`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `ladder`
--
ALTER TABLE `ladder`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Индексы таблицы `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `objects`
--
ALTER TABLE `objects`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `objects_users`
--
ALTER TABLE `objects_users`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Индексы таблицы `users_data`
--
ALTER TABLE `users_data`
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Индексы таблицы `weapons`
--
ALTER TABLE `weapons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Индексы таблицы `weapons_stats`
--
ALTER TABLE `weapons_stats`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `weapons_users`
--
ALTER TABLE `weapons_users`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `zone`
--
ALTER TABLE `zone`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `background`
--
ALTER TABLE `background`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `background_users`
--
ALTER TABLE `background_users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `banned`
--
ALTER TABLE `banned`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `chat`
--
ALTER TABLE `chat`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `dialogs`
--
ALTER TABLE `dialogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `fights`
--
ALTER TABLE `fights`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `fights_boss`
--
ALTER TABLE `fights_boss`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `fights_boss_awards`
--
ALTER TABLE `fights_boss_awards`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `fights_members`
--
ALTER TABLE `fights_members`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `fights_users`
--
ALTER TABLE `fights_users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `forum`
--
ALTER TABLE `forum`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `forum_topics`
--
ALTER TABLE `forum_topics`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `friends`
--
ALTER TABLE `friends`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `groups`
--
ALTER TABLE `groups`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `groups_logs`
--
ALTER TABLE `groups_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `groups_users`
--
ALTER TABLE `groups_users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `ladder`
--
ALTER TABLE `ladder`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `objects`
--
ALTER TABLE `objects`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `objects_users`
--
ALTER TABLE `objects_users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `weapons`
--
ALTER TABLE `weapons`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT для таблицы `weapons_stats`
--
ALTER TABLE `weapons_stats`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT для таблицы `weapons_users`
--
ALTER TABLE `weapons_users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `zone`
--
ALTER TABLE `zone`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
