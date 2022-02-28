#script for testing supporting modules in ./Plant-Nanny/supporting_modules
import unittest


"""
TESTS:
        -   
"""

class Test_Logging(unittest.TestCase):
    import Plant-Nanny/supporting_modules/logger as l
    
    def test_local_logs_exist(self):
        