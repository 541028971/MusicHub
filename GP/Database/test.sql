-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2023-12-19 03:42:15
-- 服务器版本： 10.4.28-MariaDB
-- PHP 版本： 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `test`
--

-- --------------------------------------------------------

--
-- 表的结构 `announcement`
--

CREATE TABLE `announcement` (
  `aid` int(5) NOT NULL,
  `srcuid` int(5) NOT NULL,
  `desuid` int(5) NOT NULL,
  `time` datetime NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- 表的结构 `comment`
--

CREATE TABLE `comment` (
  `cid` int(5) NOT NULL,
  `uid` int(5) NOT NULL,
  `s_id` int(5) NOT NULL,
  `content` text NOT NULL,
  `good` int(11) NOT NULL,
  `bad` int(11) NOT NULL,
  `time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- 表的结构 `favourite`
--

CREATE TABLE `favourite` (
  `uid` int(5) NOT NULL,
  `s_id` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- 表的结构 `feedback`
--

CREATE TABLE `feedback` (
  `fid` int(5) NOT NULL,
  `srcuid` int(5) NOT NULL,
  `desuid` int(5) NOT NULL,
  `time` datetime NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- 表的结构 `history`
--

CREATE TABLE `history` (
  `uid` int(5) NOT NULL,
  `s_id` int(5) NOT NULL,
  `time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 转存表中的数据 `history`
--

INSERT INTO `history` (`uid`, `s_id`, `time`) VALUES
(1, 9, '2023-12-18 21:41:52');

-- --------------------------------------------------------

--
-- 表的结构 `invitation`
--

CREATE TABLE `invitation` (
  `iid` int(5) NOT NULL,
  `code` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- 表的结构 `playlist`
--

CREATE TABLE `playlist` (
  `pid` int(5) NOT NULL,
  `uid` int(5) NOT NULL,
  `pname` varchar(100) NOT NULL,
  `pcover` varchar(100) NOT NULL,
  `private` tinyint(1) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `playlist_songs`
--

CREATE TABLE `playlist_songs` (
  `pid` int(5) NOT NULL,
  `s_id` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- 表的结构 `songs`
--

CREATE TABLE `songs` (
  `s_id` int(5) NOT NULL,
  `sname` varchar(100) NOT NULL,
  `album` varchar(100) NOT NULL,
  `lyrics` longtext DEFAULT 'Pure Music',
  `cover` varchar(100) NOT NULL DEFAULT 'images/Cover',
  `arrangement` varchar(100) NOT NULL,
  `stype` varchar(100) NOT NULL,
  `sintroduction` mediumtext NOT NULL DEFAULT 'No Introduction',
  `release_time` date NOT NULL,
  `link` varchar(100) NOT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `download` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- 转存表中的数据 `songs`
--

INSERT INTO `songs` (`s_id`, `sname`, `album`, `lyrics`, `cover`, `arrangement`, `stype`, `sintroduction`, `release_time`, `link`, `views`, `download`) VALUES
(5, '反方向的钟', 'Jay', '迷迷蒙蒙 你给的梦\r\n出现裂缝 隐隐作痛\r\n怎么沟通你都没空\r\n说我不懂 说了没用\r\n他的笑容 有何不同\r\n在你心中 我不再受宠\r\n我的天空 是雨是风\r\n还是彩虹 你在操纵\r\n恨自己真的没用\r\n', 'images/Cover/反方向的钟.png\r\n', '周杰伦', 'R&B', '《反方向的钟》是周杰伦第一张音乐专辑《Jay》中的第十首歌曲，也是专辑中唯一不具西方味道的曲，前奏并加入母语音标ㄅㄆㄇㄈ，除了B段加入奏鸣曲的感觉换了一些和弦之外，整首竟以3个和弦完成，兼具东方忧郁的', '2000-11-07', 'https://y.qq.com/n/ryqq/songDetail/0042IRwC00mCvL', 37, NULL),
(6, '最伟大的作品', '最伟大的作品', '哥穿着复古西装\r\n拿着手杖 弹着魔法乐章\r\n漫步走在 莎玛丽丹\r\n被岁月 翻新的时光\r\n望不到边界的帝国\r\n用音符筑成的王座\r\n我用琴键穿梭 1920错过的不朽\r\n啊 偏执是那马格利特\r\n被我变出的苹果\r\n超现实的是我\r\n还是他原本想画的小丑\r\n不是烟斗的烟斗\r\n脸上的鸽子没有飞走\r\n请你记得\r\n他是个画家不是什么调酒', 'images/Cover/最伟大的作品.png', '周杰伦', 'Classical Rap', '《最伟大的作品》曲风巧妙优雅地融合了古典、嘻哈、饶舌与流行，内外体现出了一种复古气质', '2022-07-06', 'https://y.qq.com/n/ryqq/songDetail/361947418', 17, NULL),
(7, '亡き王女の為のセプテット', '東方紅魔郷～the Embodiment of Scarlet Devil.', 'Pure Music', 'images/Cover/亡き王女の為のセプテット.png', '上海アリス幻樂団', 'Genso', 'レミリア・スカーレットのテーマです。これがラストだ！といわんばかりの曲を目指しました。あんまり重厚さを出したり不気味さを出したり、そういうありがちラストは嫌なので、ジャズフュージョンチックにロリっぽさを混ぜて．．．、ってそれじゃいつもとあんまり変わらんな。このメロディは自分でも理解しやすく、気に入っています。', '2002-08-11', 'https://music.163.com/song?id=22636723', 241, 'audios/Download/亡き王女の為のセプテット.mp3'),
(9, '竹取飛翔　～ Lunatic Princess', '東方永夜抄～Imperishable Night.', 'Pure Music', 'images/Cover/竹取飛翔　～ Lunatic Princess.png', '上海アリス幻樂団', 'Genso', '蓬莱山 輝夜のテーマです。キテるなぁ（笑）　冷静さを欠いた感情剥き出しの曲です。曲で感情を表現するのではなく、感情で曲を創る、むしろゲームが曲を創る。なんてクールじゃない、今風じゃない曲なんだろう（笑）私はプロじゃないですからねぇ', '2004-08-15', 'https://music.163.com/song?id=22636695', 54, 'audios/Download/竹取飛翔　～ Lunatic Princess.mp3'),
(10, '千年幻想郷　～ History of the Moon', '東方永夜抄～Imperishable Night.', 'Pure Music', 'images/Cover/千年幻想郷　～ History of the Moon.png', '上海アリス幻樂団', 'Genso', '八意 永琳のテーマです。\r\nありえない程勇ましかったり、激しかったり、爽やかだったり。\r\n一つだけ共通しているテーマは、物凄く馬鹿みたいに元気である事。\r\n元気と馬鹿だけがラスボスの取得なんですから（えー）というか、こんな曲でＳＴＧって言う事自体がルナティック。\r\n表現のテーマは檻に囚われてはいけないと思う。', '2004-08-15', 'https://music.163.com/song?id=22636683', 20, 'audios/Download/千年幻想郷　～ History of the Moon.mp3'),
(11, '萃夢想', '幻想曲抜萃 東方萃夢想 ORIGINAL SOUND TRACK', 'Pure Music', 'images/Cover/萃夢想.png', '黄昏フロンティア', 'Genso', 'オープニング～タイトル曲です。東方ぽく最初は静かですがあまり盛り上がりません。最初予定になかったオープニングを無理やり作って存在価値を高めてみました。これで一回くらいは聞いて貰えそうです。私が担当した中では一番「東方の世界とはこんな感じではないか」と意識して作った曲です。', '2005-08-14', 'https://music.163.com/song?id=22765977', 33, 'audios/Download/萃夢想.mp3'),
(12, '六十年目の東方裁判　～ Fate of Sixty Years', '東方花映塚～Phantasmagoria of Flower View.', 'Pure Music', 'images/Cover/六十年目の東方裁判　～ Fate of Sixty Years.png', '上海アリス幻樂団', 'Genso', '四季映姫・ヤマザナドゥのテーマです。 　明らかにラストっぽい曲です。ラストはメロディアスな曲が多い のが東方の特徴。今回はさらに日本＋再生＋桜の国、というイメー ジを盛り込みました。 　力強さとはかなさが同居するこの曲は、未だ見られない最も美しい桜 の国のための曲です。 　全体的にお馬鹿なこのゲームも、この曲だけは強い思いで。', '2005-08-14', 'https://music.163.com/song?id=510216', 27, 'audios/Download/六十年目の東方裁判　～ Fate of Sixty Years.mp3'),
(14, 'AA', '全人類ノ天楽録', 'Pure Music', 'images/Cover/AA.png', '黄昏フロンティア', 'Genso', 'No Introduction', '2008-08-16', 'https://music.163.com/song?id=22765914', 102, 'audios/Download/AA.mp3'),
(20, '霊知の太陽信仰　～ Nuclear Fusion', '東方地霊殿〜Subterranean Animism.', 'Pure Music', 'images/Cover/霊知の太陽信仰　～ Nuclear Fusion.png', '上海アリス幻樂団', 'Genso', '霊烏路 空のテーマです。 　 　出来る限り軽く近代的で、単純にボスっぽい感じで攻めてみました。 　事実、ラスボスとしては相当軽い妖怪です。しょせん鳥だしね。 　ただ力は滅茶苦茶強いのです。強い力でも馬鹿に持たせれば悪用す 　らできないって事がよく判ります。ああ夢のエネルギーなのに。', '2008-08-16', 'https://music.163.com/song?id=22636637', 22, 'audios/Download/霊知の太陽信仰　～ Nuclear Fusion.mp3'),
(21, '只因你太美', '以团之名 第一期', '只因你太美 baby', 'images/Cover/只因你太美.png', '蔡徐坤', 'Animals', 'No Introduction', '2019-01-18', 'https://music.163.com/song?id=1340439829', 4, 'audios/Download/只因你太美.mp3'),
(22, '朝焼けのスターマイン', '朝焼けのスターマイン', 'はぐれた君を探してたよ  呼びかけた声かき消されて  僕が握り締めたその手は  震えていたね  ふたりの想いが  戾せない時間  抱きしね 高く飛んでくよ  万華鏡空にきらめいて  君がぎこちなく微笑んでて  愛しさ溢れてゆく  光が溶けてくその前に  心から願うんだ  この瞬間を  永遠に忘れないようにと  数え切れない君との日々  振り向けばほら  スターマインのよう鮮やかに  朝焼けは虹色  祭りの後ただひとり  君の余韻に浸る  ぬくもり抱いて歩き出すよ  この奇跡にありがとう  いつの日かまた  巡り会えますように  万華鏡空に煌めいて  君がぎこちなく微笑んでた  愛しさ溢れてゆく  光が溶けてくその前に  心から誓ったんだ  この瞬間を  永遠に忘れはしないと  遠く咲き乱れてる  スターマイン空彩る', 'images/Cover/朝焼けのスターマイン.png', '今井麻美', 'Memories', 'No Introduction', '2015-06-03', 'https://music.163.com/#/song?id=33684521', 19, 'audios/Download/朝焼けのスターマイン.mp3'),
(23, 'Flower Dance', 'A Cup Of Coffee', 'Pure Music', 'images/Cover/Flower Dance.png', 'Dj Okawari', 'Plants', 'No Introduction', '2010-09-29', 'https://y.qq.com/n/ryqq/songDetail/003AepR40yJdm8', 11, 'audios/Download/Flower Dance.mp3'),
(38, 'ゆめみてたのあたし', 'THANK YOU BLUE', 'ゆめみてたのあたし     天と地の丁度真ん中     浮遊する揺るがないあたし  誰もが羨むんだ     唯一絶対的な存在  こんにちは  こんばんは  おはよう  はじめまして  ありふれていて  当たり前でない  ふれあいを知りたいの  求めているから  対等の価値ある  何かをありったけ  あたしにだけ  運命奇跡導きがあって  あたしに会えたあなたは幸せ  足りないものしかない     足りないものしかみえない     あたし以外のすべて     きらめいてみえるのなんで     天と地の丁度真ん中     浮遊する揺るがないあたし  誰もが羨むんだ     唯一絶対的な存在  楽しいな楽しいよね  嬉しいな嬉しいよね  あなたもそうならみんな同じ  話しましょう何から話そう  ワクワクするね  ドキドキするね  今が一番幸せ  みんなと出会えて良かった  あたしひとりじゃないんだ     満たされた願望     これが求めてたきもち     あたしもみんなと同じ     きらめいてみえるはず     叶ったはずの夢だ     これが求めてた居場所     まだあたし以外がすべて  きらめいてみえるの  こんにちは  こんばんは  おはよう  はじめまして  ありふれていて  当たり前でない  ふれあいを知りたいの  求めているから  対等の価値ある  何かをありったけ  あたしにだけ  運命奇跡導きがあって  あなたに会えたあたしは幸せ  足りないものしかない     足りないものしか見えない     あたし以外のすべて  きらめいてみえる理由     すきスキ好き大好き     ないものねだりなあたし     いつから壊れていたんだろ     ゆめみてたのあたし     ゆめでもみれて嬉しかった', 'images/Cover/ゆめみてたのあたし.png', 'Daoko', 'Sorrowful', 'No Introduction', '2017-12-20', 'https://music.163.com/song?id=524152911', 6, 'audios/Download/ゆめみてたのあたし.mp3'),
(39, 'Never Gonna Give You Up', 'Simply The Best Of The 80s', 'Pure Music', 'images/Cover/Never Gonna Give You Up.png', 'Rick Ashley', 'R&B', 'No Introduction', '1999-01-01', 'https://music.163.com/song?id=5221167', 15, 'audios/Download/Never Gonna Give You Up.mp3'),
(40, 'Locus iste (Sanctus)', 'Visions', 'Locus iste a Deo factus est,  inaestimabile sacramentum  Sanctus!  Sanctus!  Benedictus, benedictus  qui venit in nomine benedictus  In nomine Domine  Benedictus, Benedictus  qui venit in nomine benedictus  In nomine Domine  Sanctus!  Sanctus!  Benedictus, in nomine  qui venit in nomine  Benedictus, in nomine  qui venit in nomine  Benedictus, venit in nomine  Benedictus, Benedictus  qui venit in nomine benedictus  In nomine Domine  Sanctus Dominus Deus Sabbaoth  Pleni sunt coeli et terra Gloria  Sanctus Dominus Deus Sabbaoth  Pleni sunt coeli gloria  Sanctus!  Sanctus!  Locus iste a Deo factus est,  inaestimabile sacramentum  Sanctus!', 'images/Cover/Locus iste (Sanctus).png', 'Libera', 'Religion', 'No Introduction', '2005-11-07', 'https://music.163.com/song?id=16682614', 16, 'audios/Download/Locus iste (Sanctus).mp3'),
(41, 'TestSong', 'TestAlbum', 'Pure Music', 'images/Cover/TestSong.png', 'TestArrangement', 'TestType', 'No Introduction', '2022-12-14', 'www.w3school.com', 3, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `uid` int(5) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `avatar` varchar(100) DEFAULT 'images/Avatar',
  `birth` date NOT NULL,
  `identity` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(11) NOT NULL,
  `city` varchar(100) NOT NULL,
  `membership` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`uid`, `username`, `password`, `avatar`, `birth`, `identity`, `status`, `email`, `phone_number`, `city`, `membership`) VALUES
(1, 'Ginga', 'Ginga', 'images/Avatar/Ginga.png', '2003-08-30', 'Creator', 'Enabled', 'ginga2003@163.com', '19527559812', 'Calamity', 114514),
(2, 'HTG', 'HTG', 'images/Avatar/HTG.png', '1919-08-10', 'Manager', 'Enabled', '114514@163.com', '81234567', 'Beijing', 114514),
(3, 'Test', 'Test', 'images/Avatar/Test.png', '2022-12-14', 'Member', 'Enabled', 'Test@163.com', '12345678901', 'TestCity', 5);

--
-- 转储表的索引
--

--
-- 表的索引 `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`cid`);

--
-- 表的索引 `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`fid`);

--
-- 表的索引 `invitation`
--
ALTER TABLE `invitation`
  ADD PRIMARY KEY (`iid`);

--
-- 表的索引 `playlist`
--
ALTER TABLE `playlist`
  ADD PRIMARY KEY (`pid`);

--
-- 表的索引 `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`s_id`);

--
-- 表的索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`uid`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `comment`
--
ALTER TABLE `comment`
  MODIFY `cid` int(5) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `feedback`
--
ALTER TABLE `feedback`
  MODIFY `fid` int(5) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `invitation`
--
ALTER TABLE `invitation`
  MODIFY `iid` int(5) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `playlist`
--
ALTER TABLE `playlist`
  MODIFY `pid` int(5) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `songs`
--
ALTER TABLE `songs`
  MODIFY `s_id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `uid` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
