<?php

require_once(dirname(__FILE__)."/../model/noderow.php");

class SdmXmlSqlConvert
{
    private $precount;
    private $postcount;
    private $parcount;
    
    // Contains an array of NodeRow objects
    private $rows;
    
    private function resetVars()
    {
        $this->precount = 0;
        $this->postcount = 0;
        $this->parcount = 0;
        $this->rows = array();
    }
    
    /**
     * $xml must be a SimpleXMLElement object 
     *
     * Converts an xml document into SQL rows of this format:
     * <int treeId, int pre, int post, int parent, string value>
     */
    public function convertToRows($xml)
    {
        $this->resetVars();
        
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
        
        // First convert the attributes, they are also nodes, but with @ prefixed to the attribute name
        foreach ($node->attributes() as $att_tag => $att_value)
        {
            $att_tag_pre = $this->precount++;
            $att_tag_post = $this->postcount++;
            echo htmlentities("Adding node: (".$att_tag_pre.", ".$att_tag_post.", ".$main_pre.", @".$att_tag.", "."RANDOM".")\n");
            $this->rows[] = new NodeRow($att_tag_pre, $att_tag_post, $main_pre, ('@'.$att_tag), "");
            
            $att_val_pre = $this->precount++;
            $att_val_post = $this->postcount++;
            echo htmlentities("Adding node: (".$att_val_pre.", ".$att_val_post.", ".$att_tag_pre.", #TEXT, ".((string)$att_value).")\n");
            $this->rows[] = new NodeRow($att_val_pre, $att_val_post, $att_tag_pre, "#TEXT", ((string)$att_value));
        }
        
        
        // Then recursively turn the children into rows too
        foreach ($node->children() as $child) 
        {
            $this->convertNode($child, $main_pre);
        }
        
        $main_post = $this->postcount;
        $this->postcount++;
        
        echo htmlentities("Adding node: (".$main_pre.", ".$main_post.", ".$parent_pre.", ".$main_tag.", RANDOM)\n<br/>");
        $this->rows[] = new NodeRow($main_pre, $main_post, $parent_pre, $main_tag, "");
        
        
    }

}

?>