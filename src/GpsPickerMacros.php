<?php

namespace VojtechDobes\NetteForms;

use Latte;

if (!class_exists('Latte\Compiler') && class_exists('Nette\Latte\Compiler')) {
	class_alias('Nette\Latte\Compiler', 'Latte\Compiler');
}


/**
 * Adds manual rendering of GpsPicker
 *
 * @author Vojtěch Dobeš
 */
class GpsPickerMacros extends Latte\Macros\MacroSet
{

    /**
     * @param Latte\Compiler $compiler
     */
	public static function install(Latte\Compiler $compiler): void
	{
		$me = new static($compiler);
		$me->addMacro('gpspicker', '$_gpspicker = $_form[%node.word]; $_gpspickerControl = $_gpspicker->getControl(TRUE); echo $_gpspickerControl->addAttributes(%node.array)->startTag()', 'echo $_gpspickerControl->endTag(); unset($_gpspicker, $_gpspickerControl)');
		$me->addMacro('gpspicker:input', array($me, 'macroInput'));
		$me->addMacro('gpspicker:label', array($me, 'macroLabel'), '?></label><?php');
	}


    /**
     * @param Latte\MacroNode $node
     * @param Latte\PhpWriter $writer
     * @throws Latte\CompileException
     */
	public function macroInput(Latte\MacroNode $node, Latte\PhpWriter $writer): void
	{
		while ($node->parentNode) {
			if ($node->parentNode->name == 'gpspicker') {
				return $writer->write('echo $_gpspicker->getPartialControl(%node.word)->addAttributes(%node.array)');
			}
			$node = $node->parentNode;
		}
		
		throw new CompileException('{gpspicker:input} can be used only within {gpspicker} macro.');
	}


    /**
     * @param Latte\MacroNode $node
     * @param Latte\PhpWriter $writer
     * @throws Latte\CompileException
     */
	public function macroLabel(Latte\MacroNode $node, Latte\PhpWriter $writer): void
	{
		while ($node->parentNode) {
			if ($node->parentNode->name == 'gpspicker') {
				$cmd = 'if ($_label = $_gpspicker->getPartialLabel(%node.word)) echo $_label->addAttributes(%node.array)';
				if ($node->isEmpty = (substr($node->args, -1) === '/')) {
					$node->setArgs(substr($node->args, 0, -1));
					return $writer->write($cmd);
				} else {
					return $writer->write($cmd . '->startTag()');
				}
			}
			$node = $node->parentNode;
		}

		throw new CompileException('{gpspicker:label} can be used only within {gpspicker} macro.');
	}

}
