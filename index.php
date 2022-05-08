<?php

$parent_id = "089ef556-dfff-4ff2-9733-654645be56fe";
$test = new NodeTraversal();
$test->recurseNodes($parent_id);
$test->printCount();
echo "Unique nodes are: " . $test->getCountOfUnique() . "<br/>";
[$most_referenced_node_id, $count_of_references] =  $test->getMostReferencedNodeId();
echo "Most referenced node is: {$most_referenced_node_id} with a count of {$count_of_references} <br/>";

class NodeTraversal
{
	private $counts = [];

	public function recurseNodes($node_id, $kill = false)
	{
		echo "Traversing Node ID: {$node_id}<br>";
		//For now assuming the data is always set correctly in the node json
		$this->setCount($node_id);
		$node = $this->visitNode($node_id);
		foreach ($node['child_node_ids'] as $child_node_id) {
			$this->recurseNodes($child_node_id);
		}
	}

	public function visitNode($id) {
		$json = file_get_contents("https://nodes-on-nodes-challenge.herokuapp.com/nodes/{$id}");
		$json = json_decode($json, true);
		return reset($json);
	}

	private function setCount($node_id)
	{
		if ( ! array_key_exists($node_id, $this->counts) ) {
			$this->counts[$node_id] = 1;
		} else {
			$this->counts[$node_id]++;
		}
	}

	public function printCount()
	{
		echo "<pre>";
		print_r($this->counts);
	}

	public function getCountOfUnique()
	{
		return count($this->counts);
	}

	public function getMostReferencedNodeId()
	{
		$current_count = 0;
		$return_node_id = "";
		foreach ($this->counts as $node_id => $count) {
			if ( $count > $current_count ) {
				$return_node_id = $node_id;
				$current_count = $count;
			}
		}

		return [$return_node_id, $current_count];
	}
}