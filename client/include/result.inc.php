<?php
    require_once(dirname(__FILE__).'/../model/noderow.php');
    require_once(dirname(__FILE__).'/../lib/sdm_util.php');
    require_once(dirname(__FILE__).'/../include/result_process.inc.php');

            /*
             * Peek at this array's last element.
             */
            function array_peek($array)
            {
                $result = NULL;
                
                if (!empty($array))
                {
                    $result = $array[count($array)-1];
                }
                
                return $result;   
            }
            
            function vNodeId($resultNum, $pre)
            {
                return "v_".$resultNum."_".$pre;
            }

            function tNodeId($resultNum, $pre)
            {
                return "t_".$resultNum."_".$pre;
            }

            function aNodeId($resultNum, $pre)
            {
                return "a_".$resultNum."_".$pre;
            }


?>
<h1>Results</h1>


<?php 
    if(isset($_SESSION['result'])) 
    {
        $result = $_SESSION['result'];
        $cid = $result['clientId'];
       
        // Check if the clientId still exists, because we will need its keys
        $qry = SqliteDb::getDb()->prepare("SELECT * FROM clients WHERE id = :client_id");
        $qry->execute(array(':client_id' => $cid));
        
        if(!$client_info = $qry->fetch())
        {
            unset($_SESSION['result']);
        }
    }
    
    if(!isset($_SESSION['result']))
    {
        echo '<p>No result available, do a <a href="?page=query">query</a> first</p>';
    }
    else 
    {
        echo '<span class="queryDescr">User: </span><span class="queryStr">'.$client_info['username'].'</span><br/>'."\n";
        echo '<span class="queryDescr">XPath query: </span><span class="queryStr">'.$result['query'].'</span><br/>'."\n";
        echo '<span class="queryDescr">Result count: </span><span class="queryStr">'.count($result['rowArrays']).'</span>'."\n";
        echo '<span class="fakeLink" id="toggleDetails">toggle query details</span>';
        echo '<div id="queryDetails" style="display: none;">';
        echo '<br/>';
        echo '<h3>Query Details</h3>';
        echo '<span class="queryDescr">TreeId: </span><span class="queryStr">'.$result['treeId'].'</span><br/>'."\n";
        echo '<span class="queryDescr">Actual Query: </span><span class="queryStr">'.$result['newQuery'].'</span><br/>'."\n";
        echo '<br/>';
        echo '<h3>Query terms: </h3>'."\n";
        echo '<table>';
        echo '<thead><tr><th>Index</th><th>E(plaintext)</th><th>f(Li)</th></tr></thead>'."\n";
        foreach ($result['newTerms'] as $index => $term)
        {
            echo '<tr><td>'.$index.'</td><td>'.$term[0].'</td><td>'.$term[1].'</td></tr>'."\n";
        }
        echo '</table>';
        echo '<br/>'."\n";
        echo '<h3>Response</h3>';
        echo '<table>';
        echo '<caption>Row details (result #1). <span class="fakeLink" id="togPlainCipher">Toggle plaintext/cipher.</span></caption>'."\n";
        echo '<thead><tr><th>pre</th><th>post</th><th>parent</th><th>tag</th><th>value</th></tr></thead>'."\n";
        echo '<tbody>';
        function togSpan($plain, $cipher)
        {
            return '<span class="toggle_plain">'.$plain.'</span><span class="toggle_cipher" style="display: none;">'.$cipher.'</span>'."\n";
        }
        
        if (count($result['rowArrays']) > 0)
        {
            $cnt = 0;
            foreach ($result['rowArrays'][0] as $row)
            {
                $node = unserialize($row);
                
                $class = (($cnt % 2) == 1) ? 'class="light"' : 'class="dark"';
                $cnt++;
                echo '<tr '.$class.'><td>'.$node->pre.'</td><td>'.$node->post.'</td><td>'.$node->parent.'</td>'."\n";
                echo '<td>'.togSpan($node->tag, $node->enctag).'</td><td>'.togSpan($node->val, $node->encval).'</td></tr>'."\n";
            }
        }
        else
        {
            echo '<tr><td colspan="5">No rows returned.</td></tr>'."\n";
        }
        echo '</tbody></table>';
        echo '</div>'."\n"."\n";
        
        echo '<script language="javascript">$("#togPlainCipher").click(function() {
            $(".toggle_plain").toggle();
            $(".toggle_cipher").toggle();
        });
        
        $("#toggleDetails").click(function() {
           $("#queryDetails").toggle("slow"); 
        });
        </script>'."\n"."\n";


                
        //echo '<pre>';
        //print_r($result);
        foreach ($result['rowArrays'] as $rowArrayIndex => $rowArray)
        {
            
            $nodeRowObjects = array();
            foreach ($rowArray as $nodeRowString)
            {
                $nodeRowObj = unserialize($nodeRowString);
                $nodeRowObjects[] = $nodeRowObj;
                //echo $nodeRowObj->toPlainString()."\n";
                //var_dump($nodeRowObj);
                //echo "\n";
            }
            

            // Find the lowest POST value
            $expectedPost = -1;
            foreach ($nodeRowObjects as $nodeRow)
            {
                if ($expectedPost == -1)
                {
                    $expectedPost = $nodeRow->post;
                }
                else
                {
                    if ($nodeRow->post < $expectedPost)
                    {
                        $expectedPost = $nodeRow->post;
                    }
                }
            }
            

            // Render this $nodeRowObjects set.
            echo '<div class="resultBlock">';
            echo '<p><strong>Result '.($rowArrayIndex+1).' of '.sizeof($result['rowArrays']).'.</strong></p>';
            echo '<div class="resultXml">';
            $level = 0;
            $stack = array();            
            // We'll use this as a stack too, reverse it so lowest pre is popped first.
            $queue = array_reverse($nodeRowObjects); 
            
            while(!empty($queue))
            {
                $nodeRow = array_pop($queue);
                
                $spacing = str_repeat('<span class="tabSpan">&nbsp;</span>', $level);
                
                // We know the root element is always a 'normal' node.
                echo $spacing.'&lt;<span class="nodeElem" id="'.tNodeId($rowArrayIndex, $nodeRow->pre).'">'.$nodeRow->tag.'</span>';
                array_push($stack, $nodeRow);
                
                // Display all attribute children.
                while(!empty($queue) && array_peek($queue)->isAttrNode())
                {
                    $attrNodeRow = array_pop($queue);
                    $attrTextRow = array_pop($queue);
                    $attTag = subStr($attrNodeRow->tag, 1);
                    echo ' <span class="nodeElem" id="'.aNodeId($rowArrayIndex, $attrNodeRow->pre).'">'.$attTag.'</span>'.
                    '=<span class="nodeElem textElem" id="'.vNodeId($rowArrayIndex, $attrTextRow->pre).'">"'.$attrTextRow->val.'"</span>';
                    $expectedPost++;
                    $expectedPost++;
                }
                
                // Now, check if the next post is this 'normal' node's close tag. That would denote an inline <close />
                if ($expectedPost == array_peek($stack)->post)
                {
                    // No more nodes... close the start node like <this /> and pop the stack
                    echo ' /&gt;'."\n<br/>";
                    $expectedPost++;
                    array_pop($stack);
                }
                else 
                {
                    // There are more nodes before the 'normal' tag closes! So they must be children. 
                      
                    //Output the tail of the starting 'normal' node, and increase the level and spacing.
                    echo '&gt;'."\n<br/>";
                    $level++;
                    $spacing = str_repeat('<span class="tabSpan">&nbsp;</span>', $level);


                    // Display the #TEXT child if present
                    while(!empty($queue) && array_peek($queue)->isTextNode())
                    {
                        $textNodeRow = array_pop($queue);
                        echo $spacing.'<span class="nodeElem textElem" id="'.vNodeId($rowArrayIndex, $textNodeRow->pre).'">'.$textNodeRow->val.'</span><br/>'."\n";
                        $expectedPost++;
                    }
                }

                // We displayed this 'normal' node + attribute and #TEXT children. Now we check if we must close it (and its parents)
                // If this 'normal' node has 'normal' children, then there is to close, otherwise there is.
                while(!empty($stack))
                {
                    $closeNode = array_peek($stack);
                    if ($closeNode->post == $expectedPost)
                    {
                        // Pop the array
                        array_pop($stack);
                        $expectedPost++;
                        $level--;
                        $spacing = str_repeat('<span class="tabSpan">&nbsp;</span>', $level);

                        echo $spacing.'&lt;/<span class="nodeElem" id="'.tNodeId($rowArrayIndex, $closeNode->pre).'">'.$closeNode->tag.'</span>&gt;<br/>'."\n";    
                    }
                    else 
                    {
                        break;
                    }
                }

                                 
                
            }  // while(!empty($queue))
            echo '</div></div>';
                        
            
        } // foreach $rowArrays
        
        //echo '</pre>';
        
    }
?>

<div id="pre_holder" style="visibility:hidden;" name="">&nbsp;</div>
<div id="type_holder" style="visibility:hidden;" name="">&nbsp;</div>

<script language="javascript">
     function urlencode(s) {
      s = encodeURIComponent(s);
      return s.replace(/~/g,'%7E').replace(/%20/g,'+');
     }

    $(".nodeElem").click(function(){
        spanId = $(this).attr("id");
        parts = spanId.split('_');
        type = parts[0];
        pre = parts[2];
        $("#pre_holder").attr("name", pre);

        strName = '';
        if (type == 'v')
        {
            // It is a value
            $("#type_holder").attr("name", 'v');
            strName = 'value';
        }
        else if (type == 'a')
        {
            // It is an attribute
            $("#type_holder").attr("name", 'a');
            strName = 'attribute name';
        }
        else
        {
            // It is a tag
            $("#type_holder").attr("name", 't');
            strName = 'tag name';
        }
        
        // Then show the prompt
        jPrompt('Enter new '+strName+':', '', 'Prompt Dialog', function(r) {
            txt = r.replace(/\s/g,"")
            if( txt != "")
            {
                
                pre = $("#pre_holder").attr("name");
                type = $("#type_holder").attr("name");
                window.location.href= window.location.href+"&action=update&type="+urlencode(type)+"&pre="+urlencode(pre)+"&txt="+urlencode(txt);
            }
        });
    })
</script>