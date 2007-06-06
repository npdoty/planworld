require File.dirname(__FILE__) + '<%= '/..' * class_nesting_depth %>/../test_helper'
require '<%= file_path %>_controller'

# Re-raise errors caught by the controller.
class <%= class_name %>Controller; def rescue_action(e) raise e end; end

context "The <%= class_name %> controller" do
  use_controller <%= class_name %>Controller
  
  xspecify "is unspecified" do
  end
end
