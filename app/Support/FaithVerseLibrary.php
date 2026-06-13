<?php

namespace App\Support;

/**
 * Prefilled Scripture pool for error, maintenance, launch, and site footer pages.
 */
final class FaithVerseLibrary
{
    /**
     * @return list<array{text: string, ref: string, only_on?: string}>
     */
    public static function all(): array
    {
        return array_merge(
            self::globalVerses(),
            self::errorVerses(),
            self::maintenanceVerses(),
            self::launchVerses(),
        );
    }

    /**
     * @return list<array{text: string, ref: string}>
     */
    public static function globalVerses(): array
    {
        return self::tag(self::globalVerseTexts(), '');
    }

    /**
     * @return list<array{text: string, ref: string, only_on: string}>
     */
    public static function errorVerses(): array
    {
        return self::tag(self::errorVerseTexts(), 'error');
    }

    /**
     * @return list<array{text: string, ref: string, only_on: string}>
     */
    public static function maintenanceVerses(): array
    {
        return self::tag(self::maintenanceVerseTexts(), 'maintenance');
    }

    /**
     * @return list<array{text: string, ref: string, only_on: string}>
     */
    public static function launchVerses(): array
    {
        return self::tag(self::launchVerseTexts(), 'launch');
    }

    /**
     * @param  list<array{text: string, ref: string}>  $verses
     * @return list<array{text: string, ref: string, only_on: string}>
     */
    private static function tag(array $verses, string $onlyOn): array
    {
        return array_map(
            fn (array $verse): array => [
                'text' => $verse['text'],
                'ref' => $verse['ref'],
                'only_on' => $onlyOn,
            ],
            $verses,
        );
    }

    /**
     * @return list<array{text: string, ref: string}>
     */
    private static function globalVerseTexts(): array
    {
        return [
            ['text' => 'Be still, and know that I am God.', 'ref' => 'Psalm 46:10'],
            ['text' => 'Come to me, all you who are weary and burdened, and I will give you rest.', 'ref' => 'Matthew 11:28'],
            ['text' => 'Peace I leave with you; my peace I give you.', 'ref' => 'John 14:27'],
            ['text' => 'The Lord is my shepherd; I shall not want.', 'ref' => 'Psalm 23:1'],
            ['text' => 'Cast all your anxiety on him because he cares for you.', 'ref' => '1 Peter 5:7'],
            ['text' => 'Trust in the Lord with all your heart and lean not on your own understanding.', 'ref' => 'Proverbs 3:5'],
            ['text' => 'The Lord bless you and keep you; the Lord make his face shine on you.', 'ref' => 'Numbers 6:24–25'],
            ['text' => 'Wait for the Lord; be strong and take heart and wait for the Lord.', 'ref' => 'Psalm 27:14'],
            ['text' => 'Draw near to God, and he will draw near to you.', 'ref' => 'James 4:8'],
            ['text' => 'The Lord gives wisdom; from his mouth come knowledge and understanding.', 'ref' => 'Proverbs 2:6'],
            ['text' => 'The Lord is good to those whose hope is in him, to the one who seeks him.', 'ref' => 'Lamentations 3:25'],
            ['text' => 'Do not be anxious about anything, but in every situation, by prayer and petition, with thanksgiving, present your requests to God.', 'ref' => 'Philippians 4:6'],
            ['text' => 'God is our refuge and strength, an ever-present help in trouble.', 'ref' => 'Psalm 46:1'],
            ['text' => 'Under his wings you will find refuge; his faithfulness will be your shield and rampart.', 'ref' => 'Psalm 91:4'],
            ['text' => 'My grace is sufficient for you, for my power is made perfect in weakness.', 'ref' => '2 Corinthians 12:9'],
            ['text' => 'The Lord is close to the brokenhearted and saves those who are crushed in spirit.', 'ref' => 'Psalm 34:18'],
            ['text' => 'He will wipe every tear from their eyes. There will be no more death or mourning or crying or pain.', 'ref' => 'Revelation 21:4'],
            ['text' => 'I have told you these things, so that in me you may have peace. In this world you will have trouble. But take heart! I have overcome the world.', 'ref' => 'John 16:33'],
            ['text' => 'So do not fear, for I am with you; do not be dismayed, for I am your God.', 'ref' => 'Isaiah 41:10'],
            ['text' => 'Never will I leave you; never will I forsake you.', 'ref' => 'Hebrews 13:5'],
            ['text' => 'Those who hope in the Lord will renew their strength.', 'ref' => 'Isaiah 40:31'],
            ['text' => 'I lift up my eyes to the mountains — where does my help come from? My help comes from the Lord.', 'ref' => 'Psalm 121:1–2'],
            ['text' => 'For God has not given us a spirit of fear, but of power, love and self-discipline.', 'ref' => '2 Timothy 1:7'],
            ['text' => 'Be strong and courageous. Do not be afraid; do not be discouraged, for the Lord your God will be with you wherever you go.', 'ref' => 'Joshua 1:9'],
            ['text' => 'Therefore do not worry about tomorrow, for tomorrow will worry about itself.', 'ref' => 'Matthew 6:34'],
            ['text' => 'May the God of hope fill you with all joy and peace as you trust in him.', 'ref' => 'Romans 15:13'],
            ['text' => 'Let the peace of Christ rule in your hearts.', 'ref' => 'Colossians 3:15'],
            ['text' => 'The Lord is good, a refuge in times of trouble.', 'ref' => 'Nahum 1:7'],
            ['text' => 'The Lord your God is with you, the Mighty Warrior who saves. He will take great delight in you; in his love he will no longer rebuke you, but will rejoice over you with singing.', 'ref' => 'Zephaniah 3:17'],
            ['text' => 'You will keep in perfect peace those whose minds are steadfast, because they trust in you.', 'ref' => 'Isaiah 26:3'],
            ['text' => 'The Lord himself goes before you and will be with you; he will never leave you nor forsake you.', 'ref' => 'Deuteronomy 31:8'],
            ['text' => 'Cast your cares on the Lord and he will sustain you.', 'ref' => 'Psalm 55:22'],
            ['text' => 'Blessed are those who mourn, for they will be comforted.', 'ref' => 'Matthew 5:4'],
            ['text' => 'The grace of the Lord Jesus be with God\'s people. Amen.', 'ref' => 'Revelation 22:21'],
            ['text' => 'There is no fear in love. But perfect love drives out fear.', 'ref' => '1 John 4:18'],
            ['text' => 'Since we have been justified through faith, we have peace with God through our Lord Jesus Christ.', 'ref' => 'Romans 5:1'],
            ['text' => 'But the fruit of the Spirit is love, joy, peace, forbearance, kindness, goodness, faithfulness.', 'ref' => 'Galatians 5:22'],
            ['text' => 'Do not be afraid, little flock, for your Father has been pleased to give you the kingdom.', 'ref' => 'Luke 12:32'],
            ['text' => 'Let us then approach God\'s throne of grace with confidence, so that we may receive mercy and find grace to help us in our time of need.', 'ref' => 'Hebrews 4:16'],
            ['text' => 'Your word is a lamp for my feet, a light on my path.', 'ref' => 'Psalm 119:105'],
            ['text' => 'And we know that in all things God works for the good of those who love him.', 'ref' => 'Romans 8:28'],
            ['text' => 'Surely goodness and mercy shall follow me all the days of my life, and I shall dwell in the house of the Lord forever.', 'ref' => 'Psalm 23:6'],
            ['text' => 'He heals the brokenhearted and binds up their wounds.', 'ref' => 'Psalm 147:3'],
            ['text' => 'The Lord is my light and my salvation — whom shall I fear?', 'ref' => 'Psalm 27:1'],
            ['text' => 'In all your ways submit to him, and he will make your paths straight.', 'ref' => 'Proverbs 3:6'],
            ['text' => 'For I know the plans I have for you, declares the Lord, plans to prosper you and not to harm you, plans to give you hope and a future.', 'ref' => 'Jeremiah 29:11'],
            ['text' => 'The Lord will fight for you; you need only to be still.', 'ref' => 'Exodus 14:14'],
            ['text' => 'He gives strength to the weary and increases the power of the weak.', 'ref' => 'Isaiah 40:29'],
            ['text' => 'The Lord is compassionate and gracious, slow to anger, abounding in love.', 'ref' => 'Psalm 103:8'],
            ['text' => 'Because of the Lord\'s great love we are not consumed, for his compassions never fail.', 'ref' => 'Lamentations 3:22'],
            ['text' => 'Great is thy faithfulness; thy mercies begin afresh each morning.', 'ref' => 'Lamentations 3:23'],
            ['text' => 'The Lord is near to all who call on him, to all who call on him in truth.', 'ref' => 'Psalm 145:18'],
            ['text' => 'If any of you lacks wisdom, you should ask God, who gives generously to all without finding fault.', 'ref' => 'James 1:5'],
            ['text' => 'Commit to the Lord whatever you do, and he will establish your plans.', 'ref' => 'Proverbs 16:3'],
            ['text' => 'Taste and see that the Lord is good; blessed is the one who takes refuge in him.', 'ref' => 'Psalm 34:8'],
            ['text' => 'The Lord watches over you — the Lord is your shade at your right hand.', 'ref' => 'Psalm 121:5'],
            ['text' => 'He will not let your foot slip — he who watches over you will not slumber.', 'ref' => 'Psalm 121:3'],
            ['text' => 'I can do all this through him who gives me strength.', 'ref' => 'Philippians 4:13'],
            ['text' => 'And the peace of God, which transcends all understanding, will guard your hearts and your minds in Christ Jesus.', 'ref' => 'Philippians 4:7'],
            ['text' => 'For it is by grace you have been saved, through faith — and this is not from yourselves, it is the gift of God.', 'ref' => 'Ephesians 2:8'],
            ['text' => 'Jesus Christ is the same yesterday and today and forever.', 'ref' => 'Hebrews 13:8'],
            ['text' => 'Every good and perfect gift is from above, coming down from the Father of the heavenly lights.', 'ref' => 'James 1:17'],
            ['text' => 'The Lord is righteous in all his ways and faithful in all he does.', 'ref' => 'Psalm 145:17'],
            ['text' => 'He has shown you, O mortal, what is good. And what does the Lord require of you? To act justly and to love mercy and to walk humbly with your God.', 'ref' => 'Micah 6:8'],
            ['text' => 'Create in me a pure heart, O God, and renew a steadfast spirit within me.', 'ref' => 'Psalm 51:10'],
            ['text' => 'Restore to me the joy of your salvation and grant me a willing spirit, to sustain me.', 'ref' => 'Psalm 51:12'],
            ['text' => 'The Lord is my rock, my fortress and my deliverer; my God is my rock, in whom I take refuge.', 'ref' => 'Psalm 18:2'],
            ['text' => 'This is the day that the Lord has made; let us rejoice and be glad in it.', 'ref' => 'Psalm 118:24'],
            ['text' => 'Give thanks to the Lord, for he is good; his love endures forever.', 'ref' => 'Psalm 107:1'],
            ['text' => 'But those who hope in the Lord will inherit the land.', 'ref' => 'Psalm 37:9'],
            ['text' => 'The Lord upholds all who fall and lifts up all who are bowed down.', 'ref' => 'Psalm 145:14'],
            ['text' => 'He refreshes my soul. He guides me along the right paths for his name\'s sake.', 'ref' => 'Psalm 23:3'],
            ['text' => 'Even though I walk through the darkest valley, I will fear no evil, for you are with me.', 'ref' => 'Psalm 23:4'],
            ['text' => 'You prepare a table before me in the presence of my enemies. You anoint my head with oil; my cup overflows.', 'ref' => 'Psalm 23:5'],
            ['text' => 'For where two or three gather in my name, there am I with them.', 'ref' => 'Matthew 18:20'],
            ['text' => 'Ask and it will be given to you; seek and you will find; knock and the door will be opened to you.', 'ref' => 'Matthew 7:7'],
            ['text' => 'Blessed are the peacemakers, for they will be called children of God.', 'ref' => 'Matthew 5:9'],
            ['text' => 'Blessed are the pure in heart, for they will see God.', 'ref' => 'Matthew 5:8'],
            ['text' => 'Blessed are the merciful, for they will be shown mercy.', 'ref' => 'Matthew 5:7'],
            ['text' => 'Blessed are the meek, for they will inherit the earth.', 'ref' => 'Matthew 5:5'],
            ['text' => 'Blessed are the poor in spirit, for theirs is the kingdom of heaven.', 'ref' => 'Matthew 5:3'],
            ['text' => 'For God so loved the world that he gave his one and only Son, that whoever believes in him shall not perish but have eternal life.', 'ref' => 'John 3:16'],
            ['text' => 'I am the resurrection and the life. The one who believes in me will live, even though they die.', 'ref' => 'John 11:25'],
            ['text' => 'I am the way and the truth and the life. No one comes to the Father except through me.', 'ref' => 'John 14:6'],
            ['text' => 'Abide in me, and I in you. As the branch cannot bear fruit by itself, unless it abides in the vine, neither can you, unless you abide in me.', 'ref' => 'John 15:4'],
            ['text' => 'If you remain in me and my words remain in you, ask whatever you wish, and it will be done for you.', 'ref' => 'John 15:7'],
            ['text' => 'Greater love has no one than this: to lay down one\'s life for one\'s friends.', 'ref' => 'John 15:13'],
            ['text' => 'Now faith is confidence in what we hope for and assurance about what we do not see.', 'ref' => 'Hebrews 11:1'],
            ['text' => 'Therefore, since we are surrounded by such a great cloud of witnesses, let us throw off everything that hinders and run with perseverance the race marked out for us.', 'ref' => 'Hebrews 12:1'],
            ['text' => 'Let us fix our eyes on Jesus, the pioneer and perfecter of faith.', 'ref' => 'Hebrews 12:2'],
            ['text' => 'Rejoice in the Lord always. I will say it again: Rejoice!', 'ref' => 'Philippians 4:4'],
            ['text' => 'Whatever is true, whatever is noble, whatever is right, whatever is pure, whatever is lovely, whatever is admirable — think about such things.', 'ref' => 'Philippians 4:8'],
            ['text' => 'I have learned the secret of being content in any and every situation, whether well fed or hungry, whether living in plenty or in want.', 'ref' => 'Philippians 4:12'],
            ['text' => 'But you, Lord, are a compassionate and gracious God, slow to anger, abounding in love and faithfulness.', 'ref' => 'Psalm 86:15'],
            ['text' => 'The Lord is my strength and my song; he has become my salvation.', 'ref' => 'Exodus 15:2'],
            ['text' => 'The Lord will keep you from all harm — he will watch over your life.', 'ref' => 'Psalm 121:7'],
            ['text' => 'From the rising of the sun to the place where it sets, the name of the Lord is to be praised.', 'ref' => 'Psalm 113:3'],
            ['text' => 'How precious to me are your thoughts, O God! How vast is the sum of them!', 'ref' => 'Psalm 139:17'],
            ['text' => 'Search me, O God, and know my heart; test me and know my anxious thoughts.', 'ref' => 'Psalm 139:23'],
            ['text' => 'Lead me in the way everlasting.', 'ref' => 'Psalm 139:24'],
        ];
    }

    /**
     * @return list<array{text: string, ref: string}>
     */
    private static function errorVerseTexts(): array
    {
        return [
            ['text' => 'When you pass through the waters, I will be with you; and when you pass through the rivers, they will not sweep over you.', 'ref' => 'Isaiah 43:2'],
            ['text' => 'The Lord is a refuge for the oppressed, a stronghold in times of trouble.', 'ref' => 'Psalm 9:9'],
            ['text' => 'God is our refuge and strength, an ever-present help in trouble.', 'ref' => 'Psalm 46:1'],
            ['text' => 'The righteous cry out, and the Lord hears them; he delivers them from all their troubles.', 'ref' => 'Psalm 34:17'],
            ['text' => 'The Lord is near to all who call on him in truth.', 'ref' => 'Psalm 145:18'],
            ['text' => 'Call on me in the day of trouble; I will deliver you, and you will honour me.', 'ref' => 'Psalm 50:15'],
            ['text' => 'We do not lose heart. Though outwardly we are wasting away, yet inwardly we are being renewed day by day.', 'ref' => '2 Corinthians 4:16'],
            ['text' => 'For our light and momentary troubles are achieving for us an eternal glory that far outweighs them all.', 'ref' => '2 Corinthians 4:17'],
            ['text' => 'So we fix our eyes not on what is seen, but on what is unseen, since what is seen is temporary, but what is unseen is eternal.', 'ref' => '2 Corinthians 4:18'],
            ['text' => 'We are hard pressed on every side, but not crushed; perplexed, but not in despair.', 'ref' => '2 Corinthians 4:8'],
            ['text' => 'The Lord is my helper; I will not be afraid. What can mere mortals do to me?', 'ref' => 'Hebrews 13:6'],
            ['text' => 'Have I not commanded you? Be strong and courageous. Do not be afraid; do not be discouraged, for the Lord your God will be with you wherever you go.', 'ref' => 'Joshua 1:9'],
            ['text' => 'When I am afraid, I put my trust in you.', 'ref' => 'Psalm 56:3'],
            ['text' => 'In God I trust and am not afraid. What can man do to me?', 'ref' => 'Psalm 56:11'],
            ['text' => 'Though I walk in the midst of trouble, you preserve my life.', 'ref' => 'Psalm 138:7'],
            ['text' => 'You stretch out your hand against the anger of my foes; with your right hand you save me.', 'ref' => 'Psalm 138:7'],
            ['text' => 'The Lord will fulfil his purpose for me; your love, O Lord, endures forever.', 'ref' => 'Psalm 138:8'],
            ['text' => 'My flesh and my heart may fail, but God is the strength of my heart and my portion forever.', 'ref' => 'Psalm 73:26'],
            ['text' => 'Whom have I in heaven but you? And earth has nothing I desire besides you.', 'ref' => 'Psalm 73:25'],
            ['text' => 'It is God who arms me with strength and keeps my way secure.', 'ref' => 'Psalm 18:32'],
            ['text' => 'You, Lord, keep my lamp burning; my God turns my darkness into light.', 'ref' => 'Psalm 18:28'],
            ['text' => 'As for God, his way is perfect: the Lord\'s word is flawless; he shields all who take refuge in him.', 'ref' => 'Psalm 18:30'],
            ['text' => 'Those who know your name trust in you, for you, Lord, have never forsaken those who seek you.', 'ref' => 'Psalm 9:10'],
            ['text' => 'Why, my soul, are you downcast? Why so disturbed within me? Put your hope in God, for I will yet praise him, my Saviour and my God.', 'ref' => 'Psalm 42:11'],
            ['text' => 'Deep calls to deep in the roar of your waterfalls; all your waves and breakers have swept over me.', 'ref' => 'Psalm 42:7'],
            ['text' => 'By day the Lord directs his love, at night his song is with me — a prayer to the God of my life.', 'ref' => 'Psalm 42:8'],
            ['text' => 'The Lord will rescue me from every evil attack and will bring me safely to his heavenly kingdom.', 'ref' => '2 Timothy 4:18'],
            ['text' => 'No temptation has overtaken you except what is common to mankind. And God is faithful; he will not let you be tempted beyond what you can bear.', 'ref' => '1 Corinthians 10:13'],
            ['text' => 'Therefore I will boast all the more gladly about my weaknesses, so that Christ\'s power may rest on me.', 'ref' => '2 Corinthians 12:9'],
            ['text' => 'For when I am weak, then I am strong.', 'ref' => '2 Corinthians 12:10'],
            ['text' => 'Do not fear, for I am with you; do not be dismayed, for I am your God. I will strengthen you and help you.', 'ref' => 'Isaiah 41:10'],
            ['text' => 'I will uphold you with my righteous right hand.', 'ref' => 'Isaiah 41:10'],
            ['text' => 'Even youths grow tired and weary, and young men stumble and fall; but those who hope in the Lord will renew their strength.', 'ref' => 'Isaiah 40:30–31'],
            ['text' => 'They will soar on wings like eagles; they will run and not grow weary, they will walk and not be faint.', 'ref' => 'Isaiah 40:31'],
            ['text' => 'The Lord is good, a stronghold in the day of trouble; he knows those who take refuge in him.', 'ref' => 'Nahum 1:7'],
            ['text' => 'We wait in hope for the Lord; he is our help and our shield.', 'ref' => 'Psalm 33:20'],
            ['text' => 'In him our hearts rejoice, for we trust in his holy name.', 'ref' => 'Psalm 33:21'],
            ['text' => 'May your unfailing love be with us, Lord, even as we put our hope in you.', 'ref' => 'Psalm 33:22'],
            ['text' => 'Weeping may stay for the night, but rejoicing comes in the morning.', 'ref' => 'Psalm 30:5'],
            ['text' => 'You are my hiding place; you will protect me from trouble and surround me with songs of deliverance.', 'ref' => 'Psalm 32:7'],
            ['text' => 'Many are the woes of the wicked, but the Lord\'s unfailing love surrounds the one who trusts in him.', 'ref' => 'Psalm 32:10'],
            ['text' => 'Out of the depths I cry to you, Lord; Lord, hear my voice.', 'ref' => 'Psalm 130:1'],
            ['text' => 'Let your ears be attentive to my cry for mercy.', 'ref' => 'Psalm 130:2'],
            ['text' => 'With you there is forgiveness, so that we can, with reverence, serve you.', 'ref' => 'Psalm 130:4'],
            ['text' => 'Israel, put your hope in the Lord, for with the Lord is unfailing love and with him is full redemption.', 'ref' => 'Psalm 130:7'],
            ['text' => 'In my distress I called to the Lord; I cried to my God for help. From his temple he heard my voice.', 'ref' => 'Psalm 18:6'],
            ['text' => 'He reached down from on high and took hold of me; he drew me out of deep waters.', 'ref' => 'Psalm 18:16'],
            ['text' => 'He brought me out into a spacious place; he rescued me because he delighted in me.', 'ref' => 'Psalm 18:19'],
            ['text' => 'The Lord lives! Praise be to my Rock! Exalted be God my Saviour!', 'ref' => 'Psalm 18:46'],
            ['text' => 'If your law had not been my delight, I would have perished in my affliction.', 'ref' => 'Psalm 119:92'],
            ['text' => 'Before I was afflicted I went astray, but now I obey your word.', 'ref' => 'Psalm 119:67'],
            ['text' => 'It was good for me to be afflicted so that I might learn your decrees.', 'ref' => 'Psalm 119:71'],
            ['text' => 'Trouble and distress have come upon me, but your commands give me delight.', 'ref' => 'Psalm 119:143'],
            ['text' => 'Answer me when I call to you, my righteous God. Give me relief from my distress; have mercy on me and hear my prayer.', 'ref' => 'Psalm 4:1'],
            ['text' => 'In peace I will lie down and sleep, for you alone, Lord, make me dwell in safety.', 'ref' => 'Psalm 4:8'],
            ['text' => 'Keep me as the apple of your eye; hide me in the shadow of your wings.', 'ref' => 'Psalm 17:8'],
            ['text' => 'You are my refuge from the storm and my shelter from the heat.', 'ref' => 'Isaiah 25:4'],
            ['text' => 'You have been a refuge for the poor, a refuge for the needy in their distress, a shelter from the storm.', 'ref' => 'Isaiah 25:4'],
            ['text' => 'The Lord will surely comfort Zion and will look with compassion on all her ruins.', 'ref' => 'Isaiah 51:3'],
            ['text' => 'Joy and gladness will be found in her, thanksgiving and the sound of singing.', 'ref' => 'Isaiah 51:3'],
            ['text' => 'Do not grieve, for the joy of the Lord is your strength.', 'ref' => 'Nehemiah 8:10'],
        ];
    }

    /**
     * @return list<array{text: string, ref: string}>
     */
    private static function maintenanceVerseTexts(): array
    {
        return [
            ['text' => 'Be still before the Lord and wait patiently for him.', 'ref' => 'Psalm 37:7'],
            ['text' => 'Wait for the Lord; be strong and take heart and wait for the Lord.', 'ref' => 'Psalm 27:14'],
            ['text' => 'Be still, and know that I am God.', 'ref' => 'Psalm 46:10'],
            ['text' => 'The Lord is good to those whose hope is in him, to the one who seeks him.', 'ref' => 'Lamentations 3:25'],
            ['text' => 'It is good to wait quietly for the salvation of the Lord.', 'ref' => 'Lamentations 3:26'],
            ['text' => 'But if we hope for what we do not yet have, we wait for it patiently.', 'ref' => 'Romans 8:25'],
            ['text' => 'Let us not become weary in doing good, for at the proper time we will reap a harvest if we do not give up.', 'ref' => 'Galatians 6:9'],
            ['text' => 'Being confident of this, that he who began a good work in you will carry it on to completion until the day of Christ Jesus.', 'ref' => 'Philippians 1:6'],
            ['text' => 'He who watches over Israel will neither slumber nor sleep.', 'ref' => 'Psalm 121:4'],
            ['text' => 'Unless the Lord builds the house, the builders labour in vain.', 'ref' => 'Psalm 127:1'],
            ['text' => 'Unless the Lord watches over the city, the guards stand watch in vain.', 'ref' => 'Psalm 127:1'],
            ['text' => 'In vain you rise early and stay up late, toiling for food to eat — for he grants sleep to those he loves.', 'ref' => 'Psalm 127:2'],
            ['text' => 'The Lord will guide you always; he will satisfy your needs in a sun-scorched land and will strengthen your frame.', 'ref' => 'Isaiah 58:11'],
            ['text' => 'You will be like a well-watered garden, like a spring whose waters never fail.', 'ref' => 'Isaiah 58:11'],
            ['text' => 'See, I am doing a new thing! Now it springs up; do you not perceive it?', 'ref' => 'Isaiah 43:19'],
            ['text' => 'I am making a way in the wilderness and streams in the wasteland.', 'ref' => 'Isaiah 43:19'],
            ['text' => 'For I am about to do something new.', 'ref' => 'Isaiah 43:19'],
            ['text' => 'Restore us, O God; make your face shine on us, that we may be saved.', 'ref' => 'Psalm 80:3'],
            ['text' => 'He restores my soul. He guides me along the right paths for his name\'s sake.', 'ref' => 'Psalm 23:3'],
            ['text' => 'Create in me a pure heart, O God, and renew a steadfast spirit within me.', 'ref' => 'Psalm 51:10'],
            ['text' => 'The Lord is my shepherd, I lack nothing. He makes me lie down in green pastures, he leads me beside quiet waters.', 'ref' => 'Psalm 23:1–2'],
            ['text' => 'He refreshes my soul.', 'ref' => 'Psalm 23:3'],
            ['text' => 'Trust in the Lord with all your heart and lean not on your own understanding.', 'ref' => 'Proverbs 3:5'],
            ['text' => 'In all your ways submit to him, and he will make your paths straight.', 'ref' => 'Proverbs 3:6'],
            ['text' => 'The Lord will fight for you; you need only to be still.', 'ref' => 'Exodus 14:14'],
            ['text' => 'My times are in your hands.', 'ref' => 'Psalm 31:15'],
            ['text' => 'Into your hands I commit my spirit; deliver me, Lord, my faithful God.', 'ref' => 'Psalm 31:5'],
            ['text' => 'I remain confident of this: I will see the goodness of the Lord in the land of the living.', 'ref' => 'Psalm 27:13'],
            ['text' => 'Wait for the Lord; be strong and take heart and wait for the Lord.', 'ref' => 'Psalm 27:14'],
            ['text' => 'But they who wait for the Lord shall renew their strength.', 'ref' => 'Isaiah 40:31'],
            ['text' => 'The Lord is not slow in keeping his promise, as some understand slowness. Instead he is patient with you, not wanting anyone to perish.', 'ref' => '2 Peter 3:9'],
            ['text' => 'Let us hold unswervingly to the hope we profess, for he who promised is faithful.', 'ref' => 'Hebrews 10:23'],
            ['text' => 'Let us consider how we may spur one another on toward love and good deeds.', 'ref' => 'Hebrews 10:24'],
            ['text' => 'Let us not give up meeting together, as some are in the habit of doing, but let us encourage one another.', 'ref' => 'Hebrews 10:25'],
            ['text' => 'Every house is built by someone, but God is the builder of everything.', 'ref' => 'Hebrews 3:4'],
            ['text' => 'By wisdom a house is built, and through understanding it is established.', 'ref' => 'Proverbs 24:3'],
            ['text' => 'Through knowledge its rooms are filled with rare and beautiful treasures.', 'ref' => 'Proverbs 24:4'],
            ['text' => 'The Lord works out everything to its proper end — even the wicked for a day of disaster.', 'ref' => 'Proverbs 16:4'],
            ['text' => 'Commit to the Lord whatever you do, and he will establish your plans.', 'ref' => 'Proverbs 16:3'],
            ['text' => 'Many are the plans in a person\'s heart, but it is the Lord\'s purpose that prevails.', 'ref' => 'Proverbs 19:21'],
            ['text' => 'I waited patiently for the Lord; he turned to me and heard my cry.', 'ref' => 'Psalm 40:1'],
            ['text' => 'He lifted me out of the slimy pit, out of the mud and mire; he set my feet on a rock and gave me a firm place to stand.', 'ref' => 'Psalm 40:2'],
            ['text' => 'He put a new song in my mouth, a hymn of praise to our God.', 'ref' => 'Psalm 40:3'],
            ['text' => 'As you know, we count as blessed those who have persevered.', 'ref' => 'James 5:11'],
            ['text' => 'You have heard of Job\'s perseverance and have seen what the Lord finally brought about.', 'ref' => 'James 5:11'],
            ['text' => 'Be patient, then, brothers and sisters, until the Lord\'s coming.', 'ref' => 'James 5:7'],
            ['text' => 'See how the farmer waits for the land to yield its valuable crop, patiently waiting for the autumn and spring rains.', 'ref' => 'James 5:7'],
            ['text' => 'You too, be patient and stand firm, because the Lord\'s coming is near.', 'ref' => 'James 5:8'],
            ['text' => 'You need to persevere so that when you have done the will of God, you will receive what he has promised.', 'ref' => 'Hebrews 10:36'],
            ['text' => 'Since ancient times no one has heard, no ear has perceived, no eye has seen any God besides you, who acts on behalf of those who wait for him.', 'ref' => 'Isaiah 64:4'],
            ['text' => 'Teach us to number our days, that we may gain a heart of wisdom.', 'ref' => 'Psalm 90:12'],
            ['text' => 'Make us glad for as many days as you have afflicted us, for as many years as we have seen trouble.', 'ref' => 'Psalm 90:15'],
            ['text' => 'May the favour of the Lord our God rest on us; establish the work of our hands for us — yes, establish the work of our hands.', 'ref' => 'Psalm 90:17'],
            ['text' => 'The Lord will fulfill his purpose for me; your love, O Lord, endures forever — do not abandon the works of your hands.', 'ref' => 'Psalm 138:8'],
            ['text' => 'Let us not become weary in doing good, for at the proper time we will reap a harvest if we do not give up.', 'ref' => 'Galatians 6:9'],
            ['text' => 'Therefore, my dear brothers and sisters, stand firm. Let nothing move you. Always give yourselves fully to the work of the Lord.', 'ref' => '1 Corinthians 15:58'],
            ['text' => 'Because you know that your labour in the Lord is not in vain.', 'ref' => '1 Corinthians 15:58'],
            ['text' => 'The Lord is faithful to all his promises and loving toward all he has made.', 'ref' => 'Psalm 145:13'],
            ['text' => 'He will not let your foot slip — he who watches over you will not slumber.', 'ref' => 'Psalm 121:3'],
            ['text' => 'The sun will not harm you by day, nor the moon by night. The Lord will watch over your coming and going both now and forevermore.', 'ref' => 'Psalm 121:6–8'],
            ['text' => 'Be strong and take heart, all you who hope in the Lord.', 'ref' => 'Psalm 31:24'],
            ['text' => 'But as for me, I watch in hope for the Lord, I wait for God my Saviour; my God will hear me.', 'ref' => 'Micah 7:7'],
        ];
    }

    /**
     * @return list<array{text: string, ref: string}>
     */
    private static function launchVerseTexts(): array
    {
        return [
            ['text' => 'See, I am doing a new thing! Now it springs up; do you not perceive it?', 'ref' => 'Isaiah 43:19'],
            ['text' => 'For I know the plans I have for you, declares the Lord, plans to give you hope and a future.', 'ref' => 'Jeremiah 29:11'],
            ['text' => 'This is the day that the Lord has made; let us rejoice and be glad in it.', 'ref' => 'Psalm 118:24'],
            ['text' => 'The Lord has done great things for us, and we are filled with joy.', 'ref' => 'Psalm 126:3'],
            ['text' => 'Those who sow with tears will reap with songs of joy.', 'ref' => 'Psalm 126:5'],
            ['text' => 'They go out weeping, carrying seed to sow; they return with songs of joy, carrying sheaves with them.', 'ref' => 'Psalm 126:6'],
            ['text' => 'Restore our fortunes, Lord, like streams in the Negev.', 'ref' => 'Psalm 126:4'],
            ['text' => 'Enter his gates with thanksgiving and his courts with praise; give thanks to him and praise his name.', 'ref' => 'Psalm 100:4'],
            ['text' => 'For the Lord is good and his love endures forever; his faithfulness continues through all generations.', 'ref' => 'Psalm 100:5'],
            ['text' => 'Shout for joy to the Lord, all the earth. Worship the Lord with gladness; come before him with joyful songs.', 'ref' => 'Psalm 100:1–2'],
            ['text' => 'Know that the Lord is God. It is he who made us, and we are his; we are his people, the sheep of his pasture.', 'ref' => 'Psalm 100:3'],
            ['text' => 'How good and pleasant it is when God\'s people live together in unity!', 'ref' => 'Psalm 133:1'],
            ['text' => 'For there the Lord bestows his blessing, even life forevermore.', 'ref' => 'Psalm 133:3'],
            ['text' => 'I will give you a new heart and put a new spirit in you.', 'ref' => 'Ezekiel 36:26'],
            ['text' => 'I will put my Spirit in you and move you to follow my decrees and be careful to keep my laws.', 'ref' => 'Ezekiel 36:27'],
            ['text' => 'Therefore, if anyone is in Christ, the new creation has come: the old has gone, the new is here!', 'ref' => '2 Corinthians 5:17'],
            ['text' => 'Behold, I stand at the door and knock. If anyone hears my voice and opens the door, I will come in.', 'ref' => 'Revelation 3:20'],
            ['text' => 'Ask and it will be given to you; seek and you will find; knock and the door will be opened to you.', 'ref' => 'Matthew 7:7'],
            ['text' => 'For everyone who asks receives; the one who seeks finds; and to the one who knocks, the door will be opened.', 'ref' => 'Matthew 7:8'],
            ['text' => 'Do not be afraid, little flock, for your Father has been pleased to give you the kingdom.', 'ref' => 'Luke 12:32'],
            ['text' => 'The kingdom of God has come near. Repent and believe the good news!', 'ref' => 'Mark 1:15'],
            ['text' => 'Come, all you who are thirsty, come to the waters; and you who have no money, come, buy and eat!', 'ref' => 'Isaiah 55:1'],
            ['text' => 'Listen, listen to me, and eat what is good, and you will delight in the richest of fare.', 'ref' => 'Isaiah 55:2'],
            ['text' => 'Give ear and come to me; listen, that you may live. I will make an everlasting covenant with you.', 'ref' => 'Isaiah 55:3'],
            ['text' => 'Let us go to the house of the Lord.', 'ref' => 'Psalm 122:1'],
            ['text' => 'Our feet are standing in your gates, Jerusalem.', 'ref' => 'Psalm 122:2'],
            ['text' => 'Jerusalem is built like a city that is closely compacted together.', 'ref' => 'Psalm 122:3'],
            ['text' => 'Pray for the peace of Jerusalem: "May those who love you be secure."', 'ref' => 'Psalm 122:6'],
            ['text' => 'May there be peace within your walls and security within your citadels.', 'ref' => 'Psalm 122:7'],
            ['text' => 'For the sake of my family and friends, I will say, "Peace be within you."', 'ref' => 'Psalm 122:8'],
            ['text' => 'Sing to the Lord a new song; sing to the Lord, all the earth.', 'ref' => 'Psalm 96:1'],
            ['text' => 'Sing to the Lord, praise his name; proclaim his salvation day after day.', 'ref' => 'Psalm 96:2'],
            ['text' => 'Declare his glory among the nations, his marvellous deeds among all peoples.', 'ref' => 'Psalm 96:3'],
            ['text' => 'Great is the Lord and most worthy of praise; he is to be feared above all gods.', 'ref' => 'Psalm 96:4'],
            ['text' => 'Let the heavens rejoice, let the earth be glad; let them say among the nations, "The Lord reigns!"', 'ref' => '1 Chronicles 16:31'],
            ['text' => 'From east to west, from dawn to dusk, keep God\'s name praised.', 'ref' => 'Psalm 113:3'],
            ['text' => 'May the Lord bless you and keep you; may the Lord make his face shine on you and be gracious to you.', 'ref' => 'Numbers 6:24–25'],
            ['text' => 'May the Lord turn his face toward you and give you peace.', 'ref' => 'Numbers 6:26'],
            ['text' => 'The Lord will watch over your coming and going both now and forevermore.', 'ref' => 'Psalm 121:8'],
            ['text' => 'Surely your goodness and love will follow me all the days of my life, and I will dwell in the house of the Lord forever.', 'ref' => 'Psalm 23:6'],
            ['text' => 'Arise, shine, for your light has come, and the glory of the Lord rises upon you.', 'ref' => 'Isaiah 60:1'],
            ['text' => 'Nations will come to your light, and kings to the brightness of your dawn.', 'ref' => 'Isaiah 60:3'],
            ['text' => 'He who was seated on the throne said, "I am making everything new!"', 'ref' => 'Revelation 21:5'],
            ['text' => 'Look, I am coming soon! Blessed is the one who keeps the words of the prophecy of this scroll.', 'ref' => 'Revelation 22:7'],
            ['text' => 'Amen. Come, Lord Jesus.', 'ref' => 'Revelation 22:20'],
            ['text' => 'Prepare the way for the Lord, make straight paths for him.', 'ref' => 'Isaiah 40:3'],
            ['text' => 'Every valley shall be raised up, every mountain and hill made low; the rough ground shall become level, the rugged places a plain.', 'ref' => 'Isaiah 40:4'],
            ['text' => 'And the glory of the Lord will be revealed, and all people will see it together.', 'ref' => 'Isaiah 40:5'],
            ['text' => 'Welcome one another, therefore, just as Christ has welcomed you, for the glory of God.', 'ref' => 'Romans 15:7'],
            ['text' => 'How beautiful on the mountains are the feet of those who bring good news, who proclaim peace, who bring good tidings.', 'ref' => 'Isaiah 52:7'],
            ['text' => 'Go into all the world and preach the gospel to all creation.', 'ref' => 'Mark 16:15'],
            ['text' => 'Lift up your heads, you gates; be lifted up, you ancient doors, that the King of glory may come in.', 'ref' => 'Psalm 24:7'],
            ['text' => 'Who is this King of glory? The Lord strong and mighty, the Lord mighty in battle.', 'ref' => 'Psalm 24:8'],
            ['text' => 'Open up, you ancient gates, that the King of glory may come in.', 'ref' => 'Psalm 24:9'],
            ['text' => 'Today salvation has come to this house, because this man, too, is a son of Abraham.', 'ref' => 'Luke 19:9'],
            ['text' => 'For the Son of Man came to seek and to save the lost.', 'ref' => 'Luke 19:10'],
            ['text' => 'I bring you good news that will cause great joy for all the people.', 'ref' => 'Luke 2:10'],
            ['text' => 'Today in the town of David a Saviour has been born to you; he is the Messiah, the Lord.', 'ref' => 'Luke 2:11'],
            ['text' => 'Glory to God in the highest heaven, and on earth peace to those on whom his favour rests.', 'ref' => 'Luke 2:14'],
            ['text' => 'From the fullness of his grace we have all received one blessing after another.', 'ref' => 'John 1:16'],
            ['text' => 'For the law was given through Moses; grace and truth came through Jesus Christ.', 'ref' => 'John 1:17'],
            ['text' => 'No one has ever seen God, but the one and only Son, who is himself God and is in the closest relationship with the Father, has made him known.', 'ref' => 'John 1:18'],
            ['text' => 'Blessed are those who are invited to the wedding supper of the Lamb!', 'ref' => 'Revelation 19:9'],
            ['text' => 'Let us rejoice and be glad and give him glory!', 'ref' => 'Revelation 19:7'],
        ];
    }
}
