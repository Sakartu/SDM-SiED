from constants import DB
def get_parent(records, root):
    for record in records:
        if record[DB.TREE_PRE] == root[DB.TREE_PARENT]:
            return record

def get_descendants(records, root):
    results = []
    for record in records:
        if root[DB.TREE_PRE] < record[DB.TREE_PRE] and root[DB.TREE_POST] > record[DB.TREE_POST]:
            results.append(record)
    return results

def get_children(records, root):
    results = []
    for record in records: # then append the children
        if record[DB.TREE_PARENT] == root[DB.TREE_PRE]:
            results.append(record)
    return results

def get_all_parents(records, roots):
    result = []
    for root in roots:
        result.append(get_parent(records, root))
    return result

def get_all_children(records, roots):
    result = []
    for root in roots:
        result.extend(get_children(records, root))
    return result

def get_all_descendants(records, roots):
    result = []
    for root in roots:
        result.extend(get_descendants(records, root))
    return result
