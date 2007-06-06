ENV["RAILS_ENV"] = "test"
require File.expand_path(File.dirname(__FILE__) + "/../config/environment")
require 'test_help'

$:.unshift "#{RAILS_ROOT}/vendor/test-spec-0.3.0/lib"
require 'test/spec/rails'

begin
  # load redgreen unless running from within TextMate (in which case ANSI
  # color codes mess with the output)
  require 'redgreen' unless ENV['TM_CURRENT_LINE']
rescue LoadError
  nil
end

Test::Spec::Should.send    :alias_method, :have, :be
Test::Spec::ShouldNot.send :alias_method, :have, :be

Test::Spec::Should.class_eval do
  # Article.should.differ(:count).by(2) { blah } 
  def differ(method)
    @initial_value = @object.send(@method = method)
    self
  end

  def by(value)
    yield
    assert_equal @initial_value + value, @object.send(@method)
  end
  
  def have_association(association)
    assert @object.has_association?(association)
  end
end

class Test::Unit::TestCase
  include AuthenticatedTestHelper
  
  # Transactional fixtures accelerate your tests by wrapping each test method
  # in a transaction that's rolled back on completion.  This ensures that the
  # test database remains unchanged so your fixtures don't have to be reloaded
  # between every test method.  Fewer database queries means faster tests.
  #
  # Read Mike Clark's excellent walkthrough at
  #   http://clarkware.com/cgi/blosxom/2005/10/24#Rails10FastTesting
  #
  # Every Active Record database supports transactions except MyISAM tables
  # in MySQL.  Turn off transactional fixtures in this case; however, if you
  # don't care one way or the other, switching from MyISAM to InnoDB tables
  # is recommended.
  self.use_transactional_fixtures = true

  # Instantiated fixtures are slow, but give you @david where otherwise you
  # would need people(:david).  If you don't want to migrate your existing
  # test cases which use the @david style and don't mind the speed hit (each
  # instantiated fixtures translates to a database query per test method),
  # then set this back to true.
  self.use_instantiated_fixtures  = false

  # Add more helper methods to be used by all tests here...
end
