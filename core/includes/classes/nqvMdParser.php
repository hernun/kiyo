<?php 

use FastVolt\Helper\Markdown;

class nqvMdParser {
    protected $markdown;
    protected array $html = [];

    public function __construct() {
    
    }

    public function parseDirpathToHtml(string $path): void {
        parseDirectory($path,2,function($e){
            if (pathinfo($e,PATHINFO_EXTENSION) === 'md') {
                if(basename($e) === 'LICENSE.md') return;
                $md = file_get_contents($e);
                if (!mb_detect_encoding($md, 'UTF-8', true)) $md = mb_convert_encoding($md, 'UTF-8');
                $markdown = new Markdown();
                $markdown->setContent($md);
                $this->html[] = $markdown->getHtml();
            }
        });
    }

    public function getHtml() {
        return $this->html;
    }

    public static function getHtmlFromDirPath(string $path): string {
        $mdParser = new nqvMdParser();
        $mdParser->parseDirpathToHtml($path);
        return implode($mdParser->getHtml());
    }

    public static function getAccordionFromDirPath($path): string {
        $mdParser = new nqvMdParser();
        $mdParser->parseDirpathToHtml($path);
        return $mdParser->toAccordion();
    }

    public function toAccordion(): string {
        return nqvHtmlParser::parse(implode($this->html), [
            'format' => 'accordion'
        ]);
    }

}