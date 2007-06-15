require File.dirname(__FILE__) + '/../test_helper'
require 'core_controller'

# Re-raise errors caught by the controller.
class CoreController; def rescue_action(e) raise e end; end

context "The Core controller" do
  use_controller CoreController
  
  xspecify "is unspecified" do
  end
end
