<?php
/**
 * TransacaoForm
 *
 * @version    1.0
 * @package    control
 * @subpackage trade
 * @author     Leonardo Biffi
 */

class TransacaoForm extends TStandardForm
{
    protected $form; // form
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('database');              // defines the database
        $this->setActiveRecord('Transacao');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Transacao');
        $this->form->setFormTitle('Transações');
        
        // create the form fields
        $id = new TEntry('id');
        $descricao = new TEntry('descricao');
        $valor = new TEntry('valor');
        $data_transacao = new TDate('data_transacao');
        
        // add the fields
        $this->form->addFields( [new TLabel('ID')], [$id]);
        $this->form->addFields( [new TLabel('Descrição')], [$descricao] );
        $this->form->addFields( [new TLabel('Valor')], [$valor]);
        $this->form->addFields([new TLabel('Data')], [$data_transacao]);
        
        $id->setEditable(FALSE);
        $id->setSize('10%');
        $descricao->setSize('30%');
        $valor->setSize('30%');
        $data_transacao->setSize('30%');
        $descricao->addValidation('Descrição', new TRequiredValidator );
        $valor->addValidation('Valor', new TRequiredValidator );
        
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('Clear'),  new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addAction(_t('Back'),new TAction(array('TransacaoList','onReload')),'fa:arrow-circle-o-left blue');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'TransacaoList'));
        $container->add($this->form);
        
        parent::add($container);
    }
}
