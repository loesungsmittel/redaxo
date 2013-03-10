<?php

/**
 * The rex_article_slice class is an object wrapper over the database table rex_articel_slice.
 * Together with rex_article and rex_category it provides an object oriented
 * Framework for accessing vital parts of your website.
 * This framework can be used in Modules, Templates and PHP-Slices!
 *
 * @package redaxo\structure\content
 */
class rex_article_slice
{
    private
        $_id,
        $_article_id,
        $_clang,
        $_ctype,
        $_prior,
        $_module_id,

        $_createdate,
        $_updatedate,
        $_createuser,
        $_updateuser,
        $_revision,

        $_values,
        $_media,
        $_medialists,
        $_links,
        $_linklists;

    /**
     * Constructor
     */
    protected function __construct(
        $id, $article_id, $clang, $ctype, $module_id, $prior,
        $createdate, $updatedate, $createuser, $updateuser, $revision,
        $values, $media, $medialists, $links, $linklists)
    {
        $this->_id = $id;
        $this->_article_id = $article_id;
        $this->_clang = $clang;
        $this->_ctype = $ctype;
        $this->_prior = $prior;
        $this->_module_id = $module_id;

        $this->_createdate = $createdate;
        $this->_updatedate = $updatedate;
        $this->_createuser = $createuser;
        $this->_updateuser = $updateuser;
        $this->_revision = $revision;

        $this->_values = $values;
        $this->_media = $media;
        $this->_medialists = $medialists;
        $this->_links = $links;
        $this->_linklists = $linklists;
    }

    /**
     * Return an ArticleSlice by its id
     *
     * @returns self
     */
    public static function getArticleSliceById($an_id, $clang = false, $revision = 0)
    {
        if ($clang === false)
            $clang = rex_clang::getCurrentId();

        return self::getSlicesWhere('id=' . $an_id . ' AND clang=' . $clang . ' and revision=' . $revision)[0];
    }

    /**
     * Return the first slice for an article.
     * This can then be used to iterate over all the
     * slices in the order as they appear using the
     * getNextSlice() function.
     *
     * @returns self
     */
    public static function getFirstSliceForArticle($an_article_id, $clang = false, $revision = 0)
    {
        if ($clang === false)
            $clang = rex_clang::getCurrentId();

        foreach (range(1, 20) as $ctype) {
            $slice = self::getFirstSliceForCtype($ctype, $an_article_id, $clang, $revision);
            if ($slice !== null) {
                return $slice;
            }
        }

        return null;
    }

    /**
     * Returns the first slice of the given ctype of an article
     *
     * @returns self
     */
    public static function getFirstSliceForCtype($ctype, $an_article_id, $clang = false, $revision = 0)
    {
        if ($clang === false)
            $clang = rex_clang::getCurrentId();

        return self::getSlicesWhere(
             'article_id=' . $an_article_id . ' AND clang=' . $clang . ' AND ctype=' . $ctype . ' AND prior=1 AND revision=' . $revision
        )[0];
    }

    /**
     * Return all slices for an article that have a certain
     * clang or revision.
     *
     * @returns self[]
     */
    public static function getSlicesForArticle($an_article_id, $clang = false, $revision = 0)
    {
        if ($clang === false)
            $clang = rex_clang::getCurrentId();

        return self::getSlicesWhere('article_id=' . $an_article_id . ' AND clang=' . $clang . ' AND revision=' . $revision);
    }

    /**
     * Return all slices for an article that have a certain
     * module type.
     *
     * @returns self[]
     */
    public static function getSlicesForArticleOfType($an_article_id, $a_moduletype_id, $clang = false, $revision = 0)
    {
        if ($clang === false)
            $clang = rex_clang::getCurrentId();

        return self::getSlicesWhere('article_id=' . $an_article_id . ' AND clang=' . $clang . ' AND module_id=' . $a_moduletype_id . ' AND revision=' . $revision);
    }

    /**
     * Return the next slice for this article
     *
     * @returns self
     */
    public function getNextSlice()
    {
        return self::getSlicesWhere('prior = ' . ($this->_prior+1) . ' AND article_id=' . $this->_article_id . ' AND clang = ' . $this->_clang . ' AND ctype = ' . $this->_ctype . ' AND revision=' . $this->_revision)[0];
    }

    /**
     * @returns self
     */
    public function getPreviousSlice()
    {
        return self::getSlicesWhere('prior = ' . ($this->_prior-1) . ' AND article_id=' . $this->_article_id . ' AND clang = ' . $this->_clang . ' AND ctype = ' . $this->_ctype . ' AND revision=' . $this->_revision)[0];
    }

    /**
     * Gibt den Slice formatiert zurück
     * @since 4.1 - 29.05.2008
     *
     * @deprecated 5.0
     *
     * @see rex_article_content::getSlice()
     */
    public function getSlice()
    {
        $art = new rex_article_content();
        $art->setArticleId($this->getArticleId());
        $art->setClang($this->getClang());
        $art->setSliceRevision($this->getRevision());
        return $art->getSlice($this->getId());
    }

    /**
     * @param string $where
     * @param string $table
     * @param string $fields
     * @return self[]
     */
    protected static function getSlicesWhere($where, $table = null, $fields = null)
    {
        if (!$table)
            $table = rex::getTablePrefix() . 'article_slice';

        if (!$fields)
            $fields = '*';

        $sql = rex_sql::factory();
        // $sql->setDebug();
        $query = '
            SELECT ' . $fields . '
            FROM ' . $table . '
            WHERE ' . $where . '
            ORDER BY ctype, prior';

        $sql->setQuery($query);
        $rows = $sql->getRows();
        $slices = [];
        for ($i = 0; $i < $rows; $i++) {
            $slices[] = new self(
                $sql->getValue('id'), $sql->getValue('article_id'), $sql->getValue('clang'), $sql->getValue('ctype'), $sql->getValue('module_id'), $sql->getValue('prior'),
                $sql->getValue('createdate'), $sql->getValue('updatedate'), $sql->getValue('createuser'), $sql->getValue('updateuser'), $sql->getValue('revision'),
                [$sql->getValue('value1'), $sql->getValue('value2'), $sql->getValue('value3'), $sql->getValue('value4'), $sql->getValue('value5'), $sql->getValue('value6'), $sql->getValue('value7'), $sql->getValue('value8'), $sql->getValue('value9'), $sql->getValue('value10'), $sql->getValue('value11'), $sql->getValue('value12'), $sql->getValue('value13'), $sql->getValue('value14'), $sql->getValue('value15'), $sql->getValue('value16'), $sql->getValue('value17'), $sql->getValue('value18'), $sql->getValue('value19'), $sql->getValue('value20')],
                [$sql->getValue('media1'), $sql->getValue('media2'), $sql->getValue('media3'), $sql->getValue('media4'), $sql->getValue('media5'), $sql->getValue('media6'), $sql->getValue('media7'), $sql->getValue('media8'), $sql->getValue('media9'), $sql->getValue('media10')],
                [$sql->getValue('medialist1'), $sql->getValue('medialist2'), $sql->getValue('medialist3'), $sql->getValue('medialist4'), $sql->getValue('medialist5'), $sql->getValue('medialist6'), $sql->getValue('medialist7'), $sql->getValue('medialist8'), $sql->getValue('medialist9'), $sql->getValue('medialist10')],
                [$sql->getValue('link1'), $sql->getValue('link2'), $sql->getValue('link3'), $sql->getValue('link4'), $sql->getValue('link5'), $sql->getValue('link6'), $sql->getValue('link7'), $sql->getValue('link8'), $sql->getValue('link9'), $sql->getValue('link10')],
                [$sql->getValue('linklist1'), $sql->getValue('linklist2'), $sql->getValue('linklist3'), $sql->getValue('linklist4'), $sql->getValue('linklist5'), $sql->getValue('linklist6'), $sql->getValue('linklist7'), $sql->getValue('linklist8'), $sql->getValue('linklist9'), $sql->getValue('linklist10')]
            );

            $sql->next();
        }
        return $slices;
    }

    /**
     * @return rex_article
     */
    public function getArticle()
    {
        return rex_article::getArticleById($this->getArticleId());
    }

    public function getArticleId()
    {
        return $this->_article_id;
    }

    public function getClang()
    {
        return $this->_clang;
    }

    public function getCtype()
    {
        return $this->_ctype;
    }

    public function getRevision()
    {
        return $this->_revision;
    }

    public function getModuleId()
    {
        return $this->_module_id;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getValue($index)
    {
        if (is_int($index))
            return $this->_values[$index - 1];

        $attrName = '_' . $index;
        if (isset($this->$attrName))
            return $this->$attrName;

        return null;
    }

    public function getLink($index)
    {
        return $this->_links[$index - 1];
    }

    public function getLinkUrl($index)
    {
        return rex_getUrl($this->getLink($index));
    }

    public function getLinkList($index)
    {
        return $this->_linklists[$index - 1];
    }

    public function getMedia($index)
    {
        return $this->_media[$index - 1];
    }

    public function getMediaUrl($index)
    {
        return rex_url::media($this->getMedia($index));
    }

    public function getMediaList($index)
    {
        return $this->_medialists[$index - 1];
    }
}
