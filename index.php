<?php

$parent_id = "089ef556-dfff-4ff2-9733-654645be56fe";
$test = new NodeTraversal();
$test->recurseNodes($parent_id);
echo "<hr/>";
echo "Unique nodes are: " . $test->getCountOfUnique() . "<br/>";
[$most_referenced_node_id, $count_of_references] =  $test->getMostReferencedNodeId();
echo "Most referenced node is: {$most_referenced_node_id} with a count of {$count_of_references} <br/>";
echo "<hr/>";

$test->printCount();
$test->printNodes();

class NodeTraversal
{
	private $counts = [];
	private $nodes = [];

	/**
	 * I am using a depth first algorithm to traverse the nodes
	 */
	public function recurseNodes($node_id)
	{
		//Obviously would be removed for production, but is nice for debugging
		echo "Traversing Node ID: {$node_id}<br>";

		//Using unique because the structure isn't a tree and so we can point at the same nodes multiple times
		//This avoids infinite loops as well as a speed upgrade as I don't have to traverse nodes I've already seen
		$unique = $this->setCount($node_id);

		if ( $unique ) {
			//I'm assuming the node will have the correct keys set everytime
			//Depending on the data contract I'd probably add a validateNode method to verify we are getting correct data
				//This would also be used to prevent 404s in case the id wasn't valid
			$node = $this->visitNode($node_id);

			//I am using depth first so this method would always take the left most node and traverse down until there were no left most nodes to search
			foreach ($node['child_node_ids'] as $child_node_id) {
				$this->recurseNodes($child_node_id);
			}
		} else {
			//More just helpful debugging
			echo "Skipping node traversal of ID: {$node_id} as we've already seen it<br/>";
		}
	}

	/**
	 * Straight forward method does a get request with the ID as the param
	 * decodes the json and sends it back
	 */
	public function visitNode($id) {
		$json = file_get_contents("https://nodes-on-nodes-challenge.herokuapp.com/nodes/{$id}");
		$json = json_decode($json, true);
		$this->nodes[$id] = $json;
		return reset($json);
	}

	/**
	 * Using an associative array for O(1) lookups that's why count is setup in node_id => count
	 * @return false if we've already been to the node true if this is our first time seeing it
	 */
	private function setCount($node_id)
	{
		if ( ! array_key_exists($node_id, $this->counts) ) {
			$this->counts[$node_id] = 1;
			return true;
		} else {
			$this->counts[$node_id]++;
			return false;
		}
	}

	/**
	 * The below methods are just a way to print out the needed info
	 */
	public function printCount()
	{
		echo "<pre>";
		print_r($this->counts);
	}

	public function printNodes()
	{
		echo "<pre>";
		print_r($this->nodes);
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