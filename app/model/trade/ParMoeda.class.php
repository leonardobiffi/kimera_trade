<?php
/**
 * ParMoeda
 *
 * @version    1.0
 * @package    model
 * @subpackage trade
 * @author     Leonardo Biffi
 */

class ParMoeda extends TRecord
{
    const TABLENAME  = 'app_parmoeda';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'serial'; // {max, serial}
    
    // use SystemChangeLogTrait;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('descricao');
    }

    public function getNome($id)
    {
        $parmoeda = ParMoeda::find($id);

        return $parmoeda->descricao;
    }
}
