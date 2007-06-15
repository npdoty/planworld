require 'mojodna/attachment_migration'
require 'mojodna/required_attributes'
require 'mojodna/labeled_form_helper'
ActionView::Base.send                 :include, MojoDNA::LabeledFormHelper
ActionView::Helpers::InstanceTag.send :include, MojoDNA::LabeledInstanceTag
ActionView::Helpers::FormBuilder.send :include, MojoDNA::FormBuilderMethods