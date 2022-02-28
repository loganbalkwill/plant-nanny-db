from setuptools import setup, find_packages


with open('README.md') as f:
    readme = f.read()

with open('LICENSE.txt') as f:
    license = f.read()

setup(
    name='Plant-Nanny-DB',
    version='1.1.1',
    description='Enable read/write functionality for Plant Nanny Database',
    long_description='readme.md',
    long_description_content_type = 'text/markdown',
    author='Logan Balkwill',
    author_email='lgb0020@gmail.com',
    url='https://github.com/loganbalkwill/plant-nanny-db',
    license=license,
    packages=find_packages(exclude=('tests', 'sensors')),
    include_package_data = True
)