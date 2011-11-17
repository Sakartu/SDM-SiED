<?php

require_once(dirname(__FILE__)."/../model/noderow.php");

class SdmXmlSqlConvert
{
    private $precount;
    private $postcount;
    private $parcount;
    
    // Contains an array of NodeRow objects
    private $rows;
    
    private $treeId;
    
    private function resetVars()
    {
        $this->encryption_key = '';
        $this->hash_key = '';
        $this->precount = 0;
        $this->postcount = 0;
        $this->parcount = 0;
        $this->treeId = '';
        $this->clientId = -1;
        $this->rows = array();
    }
    
    /**
     * $xml must be a SimpleXMLElement object 
     *
     * Converts an xml document into SQL rows of this format:
     * <int treeId, int pre, int post, int parent, string tag, string value>
     * 
     * The keys must be in raw format.
     */
    public function convertToRows($encryption_key, $hash_key, $clientId, $treeId, $xml)
    {
        $this->resetVars();
        
        $this->encryption_key = $encryption_key;
        $this->hash_key = $hash_key;
        $this->clientId = $clientId;
        $this->treeId = $treeId;
        
        $this->convertNode($xml, -1);
    
        return $this->rows;
    }
    
    /**
     * convertNode does not return a value
     * @param node   Must be SimpleXMLElement object
     * @param parent The pre value of the parent (integer)
     */
    private function convertNode($node, $parent_pre)
    {
        // First: save this node's PRE value and PARENT value
        $main_pre = $this->precount++;
                
        $main_tag = $node->getName();

        //echo htmlentities("\n----------------------------------------------\nPROCESSING: (".$node->getName()." => ".((string)$node).")"."\n");
        
        // Note: text content is automatically encapsulated in #TEXT nodes
        
        // 1) Convert the attributes to rows, with @ prefixed to the attribute name
        foreach ($node->attributes() as $att_tag => $att_value)
        {
            // The tag should close after the val:
            // <@tag>
            //    <#TEXT>value</#TEXT>
            // </@tag>
            
            $att_tag_pre = $this->precount++;

            $att_val_pre = $this->precount++;
            $att_val_post = $this->postcount++;

            $att_tag_post = $this->postcount++;

            $length = rand(5, 20);
            $safe = true;
            $random_token = openssl_random_pseudo_bytes($length, $safe);

            //echo htmlentities("Adding attribute tag node: (".$this->treeId.", ".$att_tag_pre.", ".$att_tag_post.", ".$main_pre.", @".$att_tag.", ".$random_token.")\n");
            $this->rows[] = new NodeRow($this->encryption_key, $this->hash_key, $this->treeId, $att_tag_pre, $att_tag_post, $main_pre, ('@'.$att_tag), $random_token);
            
            //echo htmlentities("Adding attribute value node: (".$this->treeId.", ".$att_val_pre.", ".$att_val_post.", ".$att_tag_pre.", #TEXT, ".((string)$att_value).")\n");
            $this->rows[] = new NodeRow($this->encryption_key, $this->hash_key, $this->treeId, $att_val_pre, $att_val_post, $att_tag_pre, "#TEXT", ((string)$att_value));
        }
        
        // 2) Put the inline text in a text node, if present
        $trimmednode = trim((string)$node);
        if(!empty($trimmednode))
        {
            //echo 'NODE VALUE NOT EMPTY:'.($trimmednode)."\n";
            $text_pre = $this->precount++;
            $text_post = $this->postcount++;
            $text_val = $trimmednode;
            $text_tag = "#TEXT";
            //echo htmlentities("Adding text node: (".$this->treeId.", ".$text_pre.", ".$text_post.", ".$main_pre.", ".$text_tag.", ".$text_val.")\n");
            $this->rows[] = new NodeRow($this->encryption_key, $this->hash_key, $this->treeId, $text_pre, $text_post, $main_pre, $text_tag, $text_val);
        }
        
        // 3) Recursively turn the children into rows
        foreach ($node->children() as $child) 
        {
            $this->convertNode($child, $main_pre);
        }
        
        
        $main_post = $this->postcount;
        $this->postcount++;

        // Random token
        $length = rand(5, 20);
        $safe = true;
        $random_token = openssl_random_pseudo_bytes($length, $safe);

        //echo htmlentities("Adding node: (".$this->treeId.", ".$main_pre.", ".$main_post.", ".$parent_pre.", ".$main_tag.", ".$random_token.")\n");
        $this->rows[] = new NodeRow($this->encryption_key, $this->hash_key, $this->treeId, $main_pre, $main_post, $parent_pre, $main_tag, $random_token);
    }

}

?>