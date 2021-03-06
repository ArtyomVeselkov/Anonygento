<?php
/**
 * @category    SchumacherFM_Anonygento
 * @package     Model
 * @author      Cyrill at Schumacher dot fm
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @bugs        https://github.com/SchumacherFM/Anonygento/issues
 */
class SchumacherFM_Anonygento_Model_Random_LoremIpsum
{

    /**
     * @param int    $count
     * @param string $format
     * @param bool   $loremIpsum
     *
     * @return array|string
     */
    public function getLoremIpsum($count, $format = 'html', $loremIpsum = TRUE)
    {
        return trim($this->getContent($count, $format, $loremIpsum));
    }

    /**
     *    Copyright (c) 2009, Mathew Tinsley (tinsley@tinsology.net)
     *    All rights reserved.
     *
     *    Redistribution and use in source and binary forms, with or without
     *    modification, are permitted provided that the following conditions are met:
     *        * Redistributions of source code must retain the above copyright
     *          notice, this list of conditions and the following disclaimer.
     *        * Redistributions in binary form must reproduce the above copyright
     *          notice, this list of conditions and the following disclaimer in the
     *          documentation and/or other materials provided with the distribution.
     *        * Neither the name of the organization nor the
     *          names of its contributors may be used to endorse or promote products
     *          derived from this software without specific prior written permission.
     *
     *    THIS SOFTWARE IS PROVIDED BY MATHEW TINSLEY ''AS IS'' AND ANY
     *    EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
     *    WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
     *    DISCLAIMED. IN NO EVENT SHALL <copyright holder> BE LIABLE FOR ANY
     *    DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
     *    (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
     *    LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
     *    ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
     *    (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
     *    SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
     */

    private $_wordsPerParagraph = 100;
    private $_wordsPerSentence = 24.460;

    public function setWordsPerParagraph($wordsPer)
    {
        $this->_wordsPerParagraph = $wordsPer;
    }

    public function getContent($count, $format = 'html', $loremIpsum = TRUE)
    {
        $format = strtolower($format);

        if ($count <= 0)
            return '';

        switch ($format) {
            case 'txt':
                return $this->_getText($count, $loremIpsum);
            case 'plain':
                return $this->_getPlain($count, $loremIpsum);
            default:
                return $this->_getHTML($count, $loremIpsum);
        }
    }

    private function _getWords(&$arr, $count, $loremipsum)
    {
        $i = 0;
        if ($loremipsum) {
            $i      = 2;
            $arr[0] = 'lorem';
            $arr[1] = 'ipsum';
        }

        for ($i; $i < $count; $i++) {
            $index = array_rand($this->_words);
            $word  = $this->_words[$index];
            //echo $index . '=>' . $word . '<br />';

            if ($i > 0 && $arr[$i - 1] == $word) {
                $i--;
            } else {
                $arr[$i] = $word;
            }

        }
    }

    private function _getPlain($count, $loremipsum, $returnStr = TRUE)
    {
        $words = array();
        $this->_getWords($words, $count, $loremipsum);
        //print_r($words);

        $delta     = $count;
        $curr      = 0;
        $sentences = array();
        while ($delta > 0) {
            $senSize = $this->_gaussianSentence();
            //echo $curr . '<br />';
            if (($delta - $senSize) < 4)
                $senSize = $delta;

            $delta -= $senSize;

            $sentence = array();
            for ($i = $curr; $i < ($curr + $senSize); $i++)
                $sentence[] = $words[$i];

            $this->_punctuate($sentence);
            $curr        = $curr + $senSize;
            $sentences[] = $sentence;
        }

        if ($returnStr) {
            $output = '';
            foreach ($sentences as $s)
                foreach ($s as $w)
                    $output .= $w . ' ';

            return $output;
        } else
            return $sentences;
    }

    /**
     * @param $count
     * @param $loremIpsum
     *
     * @return string
     */
    private function _getText($count, $loremIpsum)
    {
        $sentences  = $this->_getPlain($count, $loremIpsum, FALSE);
        $paragraphs = $this->_getParagraphArr($sentences);

        $paragraphStr = array();
        foreach ($paragraphs as $p) {
            $paragraphStr[] = $this->_paragraphToString($p);
        }

        $paragraphStr[0] = "\t" . $paragraphStr[0];
        return implode(PHP_EOL . PHP_EOL . "\t", $paragraphStr);
    }

    private function _getParagraphArr($sentences)
    {
        $wordsPer    = $this->_wordsPerParagraph;
        $sentenceAvg = $this->_wordsPerSentence / 2;

        $sentenceWordsPer = $wordsPer - round($sentenceAvg);
        $total            = count($sentences);

        $paragraphs = array();
        $pCount     = 0;
        $currCount  = 0;
        $curr       = array();

        for ($i = 0; $i < $total; $i++) {
            $s = $sentences[$i];
            $currCount += count($s);
            $curr[] = $s;

            if (($currCount >= $sentenceWordsPer)
                || ($i == ($total - 1))
            ) {

                $currCount    = 0;
                $paragraphs[] = $curr;
                $curr         = array();
                //print_r($paragraphs);
            }
            //print_r($paragraphs);
        }

        return $paragraphs;
    }

    private function _getHTML($count, $loremIpsum)
    {
        $sentences  = $this->_getPlain($count, $loremIpsum, FALSE);
        $paragraphs = $this->_getParagraphArr($sentences);
        //print_r($paragraphs);

        $paragraphStr = array();
        foreach ($paragraphs as $p) {
            $paragraphStr[] = "<p>" . PHP_EOL . $this->_paragraphToString($p, TRUE) . '</p>';
        }

        //add new lines for the sake of clean code
        return implode(PHP_EOL, $paragraphStr);
    }

    private function _paragraphToString($paragraph, $htmlCleanCode = FALSE)
    {
        $paragraphStr = '';
        foreach ($paragraph as $sentence) {
            foreach ($sentence as $word)
                $paragraphStr .= $word . ' ';

            if ($htmlCleanCode)
                $paragraphStr .= PHP_EOL;
        }
        return $paragraphStr;
    }

    /*
    * Inserts commas and periods in the given
    * word array.
    */
    private function _punctuate(& $sentence)
    {
        $count                = count($sentence);
        $sentence[$count - 1] = $sentence[$count - 1] . '.';

        if ($count < 4)
            return $sentence;

        $commas = $this->_numberOfCommas($count);

        for ($i = 1; $i <= $commas; $i++) {
            $index = (int)round($i * $count / ($commas + 1));

            if ($index < ($count - 1) && $index > 0) {
                $sentence[$index] = $sentence[$index] . ',';
            }
        }
    }

    /*
    * Determines the number of commas for a
    * sentence of the given length. Average and
    * standard deviation are determined superficially
    */
    private function _numberOfCommas($len)
    {
        $avg    = (float)log($len, 6);
        $stdDev = (float)$avg / 6.000;

        return (int)round($this->_gauss_ms($avg, $stdDev));
    }

    /*
    * Returns a number on a gaussian distribution
    * based on the average word length of an english
    * sentence.
    * Statistics Source:
    *	http://hearle.nahoo.net/Academic/Maths/Sentence.html
    *	Average: 24.46
    *	Standard Deviation: 5.08
    */
    private function _gaussianSentence()
    {
        $avg    = (float)24.460;
        $stdDev = (float)5.080;

        return (int)round($this->_gauss_ms($avg, $stdDev));
    }

    /*
    * The following three functions are used to
    * compute numbers with a guassian distrobution
    * Source:
    * 	http://us.php.net/manual/en/function.rand.php#53784
    */
    private function _gauss()
    { // N(0,1)
        // returns random number with normal distribution:
        //   mean=0
        //   std dev=1

        // auxilary vars
        $x = $this->_random_0_1();
        $y = $this->_random_0_1();

        // two independent variables with normal distribution N(0,1)
        $u = sqrt(-2 * log($x)) * cos(2 * pi() * $y);
        $v = sqrt(-2 * log($x)) * sin(2 * pi() * $y);

        // i will return only one, couse only one needed
        return $u;
    }

    private function _gauss_ms($m = 0.0, $s = 1.0)
    {
        return $this->_gauss() * $s + $m;
    }

    /**
     * returns random float value between 0 and 1
     * @return float
     */
    private function _random_0_1()
    {
        return (float)(mt_rand() / mt_getrandmax());
    }

    private $_words = array(
        'lorem',
        'ipsum',
        'dolor',
        'sit',
        'amet',
        'consectetur',
        'adipiscing',
        'elit',
        'curabitur',
        'vel',
        'hendrerit',
        'libero',
        'eleifend',
        'blandit',
        'nunc',
        'ornare',
        'odio',
        'ut',
        'orci',
        'gravida',
        'imperdiet',
        'nullam',
        'purus',
        'lacinia',
        'a',
        'pretium',
        'quis',
        'congue',
        'praesent',
        'sagittis',
        'laoreet',
        'auctor',
        'mauris',
        'non',
        'velit',
        'eros',
        'dictum',
        'proin',
        'accumsan',
        'sapien',
        'nec',
        'massa',
        'volutpat',
        'venenatis',
        'sed',
        'eu',
        'molestie',
        'lacus',
        'quisque',
        'porttitor',
        'ligula',
        'dui',
        'mollis',
        'tempus',
        'at',
        'magna',
        'vestibulum',
        'turpis',
        'ac',
        'diam',
        'tincidunt',
        'id',
        'condimentum',
        'enim',
        'sodales',
        'in',
        'hac',
        'habitasse',
        'platea',
        'dictumst',
        'aenean',
        'neque',
        'fusce',
        'augue',
        'leo',
        'eget',
        'semper',
        'mattis',
        'tortor',
        'scelerisque',
        'nulla',
        'interdum',
        'tellus',
        'malesuada',
        'rhoncus',
        'porta',
        'sem',
        'aliquet',
        'et',
        'nam',
        'suspendisse',
        'potenti',
        'vivamus',
        'luctus',
        'fringilla',
        'erat',
        'donec',
        'justo',
        'vehicula',
        'ultricies',
        'varius',
        'ante',
        'primis',
        'faucibus',
        'ultrices',
        'posuere',
        'cubilia',
        'curae',
        'etiam',
        'cursus',
        'aliquam',
        'quam',
        'dapibus',
        'nisl',
        'feugiat',
        'egestas',
        'class',
        'aptent',
        'taciti',
        'sociosqu',
        'ad',
        'litora',
        'torquent',
        'per',
        'conubia',
        'nostra',
        'inceptos',
        'himenaeos',
        'phasellus',
        'nibh',
        'pulvinar',
        'vitae',
        'urna',
        'iaculis',
        'lobortis',
        'nisi',
        'viverra',
        'arcu',
        'morbi',
        'pellentesque',
        'metus',
        'commodo',
        'ut',
        'facilisis',
        'felis',
        'tristique',
        'ullamcorper',
        'placerat',
        'aenean',
        'convallis',
        'sollicitudin',
        'integer',
        'rutrum',
        'duis',
        'est',
        'etiam',
        'bibendum',
        'donec',
        'pharetra',
        'vulputate',
        'maecenas',
        'mi',
        'fermentum',
        'consequat',
        'suscipit',
        'aliquam',
        'habitant',
        'senectus',
        'netus',
        'fames',
        'quisque',
        'euismod',
        'curabitur',
        'lectus',
        'elementum',
        'tempor',
        'risus',
        'cras'
    );
}