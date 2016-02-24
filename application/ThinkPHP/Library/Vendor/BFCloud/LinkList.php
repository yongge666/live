<?php
class Node
{
	public $start;
	public $end;
	public $next;
	
	public function __construct($start, $end, $next)
	{
		$this->start = $start;
		$this->end = $end;
		$this->next = $next;
	}
}

class LinkList
{
	private $header;
	public $size;

	public function __construct()
	{
		$this->header = new Node(null,null,null);
		$size = 0;
	}
    /**
    *@param  $start $end-- add area to the list by ascending sequence
    *
	*/
    public function add($start, $end)
    {
		if ($end < $start)
		{
			return -1;
		}
        $node = $this->header;
		
        while(1)
        {
			if ($node->next == null)
			{
				$node->next = new Node($start, $end, null);
				$this->size++;
				break;
			}
			
			if ($start > $node->next->end)
			{
				$node = $node->next;
				continue;
			}
			$temp = $node->next;
			if ($end < $temp->start)
			{
				$node->next = new Node($start, $end, $temp);
				$this->size++;
			}
			else
			{
				//merge the area
				if ($start < $temp->start)
				{
					$temp->start = $start;
				}
				if ($end > $temp->end)
				{
					$temp->end = $end;
				}
			}
			break;
        }
		return 0;
    }
    /**
    *@param  $delete-- delete area from the list
    *@return true : find the area and delete success; false:do not find the area
    */
    public function delete($start, $end)
    {
		if ($end < $start)
		{
			return -1;
		}
        $node = $this->header;
		
        while($node->next != null)
        {
			if ($start > $node->next->end)
			{
				$node = $node->next;
				continue;
			}
			$temp = $node->next;
			if ($end < $temp->start)
			{
				break;
			}
			//split the area 
			else if ($start <= $temp->start && $end >= $temp->end)
			{
				$node->next = $temp->next;
				$this->size--;
			}
			else if ($start <= $temp->start && $end < $temp->end) 
			{
				$node->next->start = $end+1;
			}
			else if ($start > $temp->start && $end >= $temp->end)
			{
				$node->next->end = $start -1;
			}
			else 
			{
				$node->next = new Node($temp->$start, $start-1, $temp);
				$temp->start = $end + 1;
				$this->size++;
			}
			return true;
        }
        return false;
    }
    /**
    *@param  print list
    *
    */
	public function printList()
	{
		$node = $this->header;
        while($node->next!= null)
        {
			$node = $node->next;
		}
	}
}
?>
