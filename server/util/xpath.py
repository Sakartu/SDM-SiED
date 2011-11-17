from util.constants.DB import TREE_PRE, TREE_POST, TREE_PARENT
from db import db
def get_parents(conf, roots):
    result = []
    for root in roots:
        result.append(db.fetch_parent(conf, root))
    return result

def get_descendants(records, root):
    results = [root]
    for record in records:
        if root[TREE_PRE] < record[TREE_PRE] and root[TREE_POST] > record[TREE_POST]:
            results.append(record)
    return results

def get_children(records, root):
    results = [root]
    for record in records: # then append the children
        if record[TREE_PARENT] == root[TREE_PRE]:
            results.append(record)
    return results

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
