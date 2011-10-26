
class SigChecker(object):
    def __init__(self, conf):
        self.conf = conf

    def __call__(self, f):
        if self.conf['check_sigs']:
            #check the signature

            #then call the function again with the args
            def wrapped_f(*args):
                f(*args)
            return wrapped_f
        else:
            return f
